<?php

namespace Javaabu\Generators\Generators\Concerns;

use Javaabu\Generators\FieldTypes\DateTypeField;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\GeneratorHelpers\StringCaser;

trait GeneratesModel
{
    /**
     * get the model casts stub
     */
    protected function getModelCastsStub(): string
    {
        return property_exists($this, 'model_casts_stub') ? $this->model_casts_stub : '';
    }

    /**
     * get the model stub
     */
    protected function getModelStub(): string
    {
        return property_exists($this, 'model_stub') ? $this->model_stub : '';
    }

    /**
     * Whether soft deletes should be rendered
     */
    public function shouldRenderModelSoftDeletes(): bool
    {
        return true;
    }

    /**
     * Whether searchable should be rendered
     */
    public function shouldRenderModelSearchable(): bool
    {
        return true;
    }

    /**
     * Whether admin link name
     */
    public function shouldRenderModelAdminLinkName(): bool
    {
        return true;
    }

    /**
     * Get additional model casts
     */
    public function renderAdditionalModelCasts(): string
    {
        return '';
    }

    /**
     * Get additional model fillable
     */
    public function renderAdditionalModelFillable(): string
    {
        return '';
    }

    /**
     * Get additional model use statements
     */
    public function getAdditionalModelUseStatements(): array
    {
        return [];
    }

    public function renderModelCast(string $column, string $cast): string
    {
        return $this->getRenderer()->addIndentation("'$column' => $cast,\n", 3);
    }

    public function renderModelCastsStub(string $casts): string
    {
        $stub = $this->getModelCastsStub();

        $renderer = $this->getRenderer();

        $template = $renderer->loadStub($stub);

        return $renderer->appendMultipleContent([
            [
                'search' => "// casts\n",
                'keep_search' => false,
                'content' => $casts,
            ],
        ], $template);
    }

    public function renderModelFillable(string $column): string
    {
        return $this->getRenderer()->addIndentation("'$column',\n", 2);
    }

    /**
     * Render the model
     */
    public function renderModel(): string
    {
        $stub = $this->getModelStub();

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $render_soft_deletes = $this->shouldRenderModelSoftDeletes();
        $render_searchable = $this->shouldRenderModelSearchable();

        $use_statements = $this->getAdditionalModelUseStatements();
        $traits = '';
        $fillable = $this->renderAdditionalModelFillable();
        $casts = $this->renderAdditionalModelCasts();
        $searchable = '';
        $foreign_keys = [];
        $date_mutators = [];
        $admin_link_name = '';

        $name_field = $this->getNameField();

        if ($this->shouldRenderModelAdminLinkName() && $name_field != 'name') {
            $admin_link_name = $this->renderModelAdminLinkName($name_field);
        }

        if ($render_soft_deletes && $this->hasSoftDeletes()) {
            $use_statements[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
            $traits .=  $renderer->addIndentation("use SoftDeletes;\n", 1);
        }

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($field instanceof DateTypeField) {
                $carbon_import = 'use Carbon\\Carbon;';
                if (! in_array($carbon_import, $use_statements)) {
                    $use_statements[] = $carbon_import;
                }

                $date_mutators[] = $this->renderModelDateMutator($column, $field);
            }

            if ($field instanceof ForeignKeyField) {
                $belongs_to_import = 'use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;';
                if (! in_array($belongs_to_import, $use_statements)) {
                    $use_statements[] = $belongs_to_import;
                }

                $foreign_keys[] = $this->renderModelForeignKey($column, $field);
            }

            if ($field instanceof EnumField && $field->hasEnumClass()) {
                $enum_import = 'use ' . $field->getEnumClass() . ';';
                if (! in_array($enum_import, $use_statements)) {
                    $use_statements[] = $enum_import;
                }
            }

            if ($field->isFillable()) {
                $fillable .= $this->renderModelFillable($column);
            }

            if ($cast = $field->generateCast()) {

                if ($field->shouldQuoteCast()) {
                    $cast = "'$cast'";
                }

                $casts .= $this->renderModelCast($column, $cast);
            }

            if ($render_searchable && $field->isSearchable()) {
                $searchable .= $renderer->addIndentation("'$column',\n", 2);
            }
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => "// use statements\n",
                'keep_search' => false,
                'content' => $use_statements ? implode("\n", $use_statements) . "\n" : '',
            ],
            [
                'search' => "use HasFactory;\n",
                'keep_search' => true,
                'content' => $traits,
            ],
            [
                'search' => "protected \$fillable = [\n",
                'keep_search' => true,
                'content' => $fillable,
            ],
            [
                'search' => $renderer->addIndentation("// casts\n", 1),
                'keep_search' => false,
                'content' => $casts ? "\n" . $this->renderModelCastsStub($casts) . "\n" : "\n",
            ],
            [
                'search' => "protected \$searchable = [\n",
                'keep_search' => true,
                'content' => $searchable,
            ],
            [
                'search' => $renderer->addIndentation("// admin link name\n", 1),
                'keep_search' => false,
                'content' => $admin_link_name ? "\n" . $admin_link_name  : '',
            ],
            [
                'search' => $renderer->addIndentation("// date mutators\n", 1),
                'keep_search' => false,
                'content' => $date_mutators ? implode("\n", $date_mutators) . "\n" : '',
            ],
            [
                'search' => $renderer->addIndentation("// foreign keys\n", 1),
                'keep_search' => false,
                'content' => $foreign_keys ? "\n" . implode("\n", $foreign_keys) : '',
            ],
        ], $template);

        return $template;
    }

    /**
     * Render the admin link name
     */
    public function renderModelAdminLinkName(string $name_field): string
    {
        $stub = 'generators::Models/_adminLinkName.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->loadStub($stub);

        return $renderer->appendMultipleContent([
            [
                'search' => "{{attribute}}",
                'keep_search' => false,
                'content' => $name_field,
            ],
        ], $template);
    }

    /**
     * Render a foreign key
     */
    public function renderModelForeignKey(string $column, ForeignKeyField $field): string
    {
        $stub = 'generators::Models/_modelForeignKey.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $field->getRelatedTable());
        $template = $renderer->replaceNames($this->getTable(), $template, 'Model');

        $statement = $field->generateRelationStatement();

        return $renderer->appendMultipleContent([
            [
                'search' => "{{relationStatement}}",
                'keep_search' => false,
                'content' => $statement,
            ],
            [
                'search' => "{{relationName}}",
                'keep_search' => false,
                'content' => $field->getRelationName(),
            ],
        ], $template);
    }

    /**
     * Render a date mutator
     */
    public function renderModelDateMutator(string $column, DateTypeField $field): string
    {
        $stub_name = $field->isNullable() ? '_nullableDateMutator' : '_dateMutator';
        $stub = 'generators::Models/' . $stub_name . '.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->loadStub($stub);

        return $renderer->appendMultipleContent([
            [
                'search' => "{{attributeStudly}}",
                'keep_search' => false,
                'content' => StringCaser::studly($column),
            ],
            [
                'search' => "{{attribute}}",
                'keep_search' => false,
                'content' => $column,
            ],
        ], $template);
    }
}
