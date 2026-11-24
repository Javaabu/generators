<?php

namespace Javaabu\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Javaabu\Generators\Exceptions\MultipleTablesSuppliedException;
use Javaabu\Generators\Exceptions\TableDoesNotExistException;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Generators\Generator;
use Javaabu\Generators\Resource\MediaCollection;
use Javaabu\Generators\Resource\ResourceDefinition;
use Javaabu\Generators\Resource\ResourceOptions;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Schema\RelationResolver;
use Javaabu\Generators\Schema\SchemaInspector;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates code for a table. Prints it, or writes the files with --create.
 */
abstract class GenerateCommand extends Command
{
    /**
     * The generators this command runs.
     *
     * @return array<int, class-string<Generator>>
     */
    abstract protected function generators(ResourceDefinition $resource): array;

    protected function getArguments(): array
    {
        return [
            ['table', InputArgument::REQUIRED, 'The table to generate from'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['columns', null, InputOption::VALUE_REQUIRED, 'Only use these columns (comma separated)'],
            ['index', null, InputOption::VALUE_REQUIRED, 'The columns of the index table (comma separated)'],
            ['form', null, InputOption::VALUE_REQUIRED, 'The fields of the form (comma separated, many-to-many relations by name)'],
            ['show', null, InputOption::VALUE_REQUIRED, 'The fields of the show page (comma separated)'],
            ['parent', null, InputOption::VALUE_REQUIRED, 'Nest the resource under the table of this foreign key (e.g. category_id or categories)'],
            ['media', null, InputOption::VALUE_REQUIRED, 'Media collections: name[:type][:multiple], e.g. featured_image:image,gallery:image:multiple'],
            ['views', null, InputOption::VALUE_REQUIRED, 'blade or inertia'],
            ['translatable', null, InputOption::VALUE_OPTIONAL, 'Make the model translatable: the columns, or none to detect them', false],
            ['interactive', 'i', InputOption::VALUE_NONE, 'Choose the fields, parent, media and views with prompts'],
            ['create', 'c', InputOption::VALUE_NONE, 'Write the files instead of printing the code'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite files that exist'],
        ];
    }

    public function handle(): int
    {
        $table = (string) $this->argument('table');

        if (str_contains($table, ',')) {
            throw new MultipleTablesSuppliedException(__('generators::generators.one_table', ['tables' => $table]));
        }

        $inspector = app(SchemaInspector::class);
        $inspector->flush();

        if (! $inspector->hasTable($table)) {
            throw new TableDoesNotExistException(__('generators::generators.no_table', ['table' => $table]));
        }

        $relations = app(RelationResolver::class);

        if ($relations->isPivotTable($pivot = $inspector->table($table))) {
            $related = [$pivot->foreign_keys[0]->foreign_table, $pivot->foreign_keys[1]->foreign_table];
            sort($related);

            throw new InvalidArgumentException(__('generators::generators.pivot_table', [
                'table' => $table,
                'first' => $related[0],
                'second' => $related[1],
            ]));
        }

        $resource = $this->resolveResource($table);

        $files = $this->files($resource);

        if (! $this->option('create')) {
            $this->printFiles($files);

            return self::SUCCESS;
        }

        $this->writeFiles($files);

        return self::SUCCESS;
    }

    public function resolveResource(string $table): ResourceDefinition
    {
        $options = $this->resourceOptions();

        $resolver = app(ResourceResolver::class);
        $resource = $resolver->resolve($table, $options);

        if (! $this->option('interactive') || ! $this->input->isInteractive()) {
            return $resource;
        }

        return $resolver->resolve($table, $this->promptForOptions($resource, $options));
    }

    protected function resourceOptions(): ResourceOptions
    {
        $translatable = $this->option('translatable');

        return ResourceOptions::fromArray([
            'columns' => $this->option('columns'),
            'index' => $this->option('index'),
            'form' => $this->option('form'),
            'show' => $this->option('show'),
            'parent' => $this->option('parent'),
            'media' => $this->option('media') ?? '',
            'views' => $this->option('views'),
            'translatable' => $translatable === false ? null : ($translatable ?? true),
            'user_type' => $this->hasOption('type') ? $this->option('type') : null,
        ]);
    }

    /**
     * Ask for the choices that weren't given as options.
     */
    protected function promptForOptions(ResourceDefinition $resource, ResourceOptions $options): ResourceOptions
    {
        $choices = [];

        foreach ($resource->fields as $column => $field) {
            $choices[$column] = $field->getLabel() . " ({$column})";
        }

        foreach ($resource->belongs_to_many as $relation) {
            $choices[$relation->name] = ucfirst($relation->name) . ' (' . __('generators::generators.many_to_many') . ')';
        }

        $parent = $options->parent;

        if ($parent === null) {
            $foreign_keys = array_filter($resource->fields, fn ($field) => $field instanceof ForeignKeyField);

            if ($foreign_keys) {
                $parent = select(
                    label: __('generators::generators.prompt_parent'),
                    options: ['' => __('generators::generators.not_nested'), ...array_map(fn (ForeignKeyField $field) => $field->getRelatedTable(), $foreign_keys)],
                    default: '',
                ) ?: null;
            }
        }

        $index = $options->index ?? multiselect(
            label: __('generators::generators.prompt_index'),
            options: $choices,
            default: $resource->index_columns,
            scroll: 12,
        );

        $form = $options->form ?? multiselect(
            label: __('generators::generators.prompt_form'),
            options: $choices,
            default: $resource->form_columns,
            scroll: 12,
        );

        $show = $options->show ?? multiselect(
            label: __('generators::generators.prompt_show'),
            options: $choices,
            default: $resource->show_columns,
            scroll: 12,
        );

        $media = $options->media;

        if (! $media) {
            $media = MediaCollection::parse(text(
                label: __('generators::generators.prompt_media'),
                placeholder: 'featured_image:image, gallery:image:multiple',
                hint: __('generators::generators.prompt_media_hint'),
            ));
        }

        $views = $options->views ?? select(
            label: __('generators::generators.prompt_views'),
            options: ['blade' => 'Blade', 'inertia' => 'Inertia React'],
            default: $resource->views,
        );

        return new ResourceOptions(
            columns: $options->columns,
            index: array_values($index),
            form: array_values($form),
            show: array_values($show),
            parent: $parent,
            media: $media,
            views: $views,
            translatable: $options->translatable,
            user_type: $options->user_type,
        );
    }

    /**
     * @return array<int, GeneratedFile>
     */
    public function files(ResourceDefinition $resource): array
    {
        $files = [];

        foreach ($this->generators($resource) as $class) {
            /** @var Generator $generator */
            $generator = new $class($resource);

            foreach ($generator->files() as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param  array<int, GeneratedFile>  $files
     */
    protected function printFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->line("// {$file->relativePath()}" . ($file->isMerge() ? ' (' . __('generators::generators.added_to') . ')' : ''));
            foreach (explode("\n", rtrim($file->contents)) as $line) {
                $this->line($line);
            }

            $this->newLine();
        }
    }

    /**
     * @param  array<int, GeneratedFile>  $files
     */
    protected function writeFiles(array $files): void
    {
        $filesystem = app(Filesystem::class);
        $force = (bool) $this->option('force');

        foreach ($files as $file) {
            $path = $file->path;
            $exists = $filesystem->exists($path);

            if ($file->isMerge()) {
                $current = $exists ? $filesystem->get($path) : '';
                $contents = $file->mergeInto($current);

                if ($contents === $current) {
                    $this->components->twoColumnDetail($file->relativePath(), __('generators::generators.unchanged'));

                    continue;
                }

                $filesystem->ensureDirectoryExists(dirname($path));
                $filesystem->put($path, $contents);

                $this->components->twoColumnDetail($file->relativePath(), $exists ? __('generators::generators.updated') : __('generators::generators.created'));

                continue;
            }

            if ($exists && ! $force) {
                $this->components->twoColumnDetail($file->relativePath(), '<fg=yellow>' . __('generators::generators.exists') . '</>');

                continue;
            }

            $filesystem->ensureDirectoryExists(dirname($path));
            $filesystem->put($path, $file->contents);

            $this->components->twoColumnDetail($file->relativePath(), $exists ? __('generators::generators.overwritten') : __('generators::generators.created'));
        }
    }
}
