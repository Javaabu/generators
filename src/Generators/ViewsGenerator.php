<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\FieldTypes\Field;
use Javaabu\GeneratorHelpers\StringCaser;

class ViewsGenerator extends BaseGenerator
{

    public function viewsToRender(): array
    {
        return [
            'renderLayout' => StringCaser::pluralKebab($this->getTable()) . '.blade.php',
            'renderActions' => '_actions.blade.php',
            'renderForm' => '_form.blade.php',
            'renderCreateView' => 'create.blade.php',
            'renderEditView' => 'edit.blade.php',
            'renderInfolist' => '_details.blade.php',
            'renderShowView' => 'show.blade.php',
            'renderBulkActions' => '_bulk.blade.php',
            'renderTableRows' => '_list.blade.php',
            'renderTable' => '_table.blade.php',
            'renderFilters' => '_filter.blade.php',
            'renderIndexView' => 'index.blade.php',
        ];
    }

    /**
     * Render the views
     */
    public function render(): string
    {

        $output = '';

        $views = $this->viewsToRender();

        foreach ($views as $method => $file_name) {
            $output .= "// $file_name\n";
            $output .= $this->{$method}();
        }

        return $output;
    }

    /**
     * Render the info list
     */
    public function renderTableTitles(): string
    {
        $renderer = $this->getRenderer();

        $table_columns = [];
        $name_field = $this->getNameField();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($column != $name_field) {
                $table_columns[] = $renderer->addIndentation($this->getTableTitleComponentBlade($column), 2);
            }
        }

        return $table_columns ? implode("\n", $table_columns) . "\n" : '';
    }

    /**
     * Render the info list
     */
    public function renderTableColumns(): string
    {
        $renderer = $this->getRenderer();

        $table_columns = [];
        $name_field = $this->getNameField();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($column != $name_field) {
                $table_columns[] = $renderer->addIndentation($this->getTableCellComponentBlade($column) . "\n", 2);
            }
        }

        return $table_columns ? implode("\n", $table_columns) : '';
    }

    /**
     * Render the info list
     */
    public function renderInfolist(): string
    {
        $stub = 'generators::views/model/_details.blade.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());
        $form_components = [];

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            $form_components[] = $renderer->addIndentation($this->getEntryComponentBlade($column) . "\n", 1);
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => $renderer->addIndentation("// entries\n", 1),
                'keep_search' => false,
                'content' => $form_components ? implode("\n", $form_components) : '',
            ],
        ], $template);

        return $template;
    }

    /**
     * Render the form
     */
    public function renderForm(): string
    {
        $stub = 'generators::views/model/_form.blade.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());
        $form_components = [];

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            $form_components[] = $renderer->addIndentation($this->getFormComponentBlade($column) . "\n", 1);
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => "<x-forms::card>\n",
                'keep_search' => true,
                'content' => $form_components ? implode("\n", $form_components) . "\n" : '',
            ],
        ], $template);

        return $template;
    }

    /**
     * Render the layout
     */
    public function renderLayout(): string
    {
        $stub = 'generators::views/model/model.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderCreateView(): string
    {
        $stub = 'generators::views/model/create.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderEditView(): string
    {
        $stub = 'generators::views/model/edit.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderShowView(): string
    {
        $stub = 'generators::views/model/show.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderIndexView(): string
    {
        $stub = 'generators::views/model' . ($this->hasSoftDeletes() ? '-soft-deletes' : '') . '/index.blade.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        return $renderer->appendMultipleContent([
            [
                'search' => '{{icon}}',
                'keep_search' => false,
                'content' => $this->getIcon(),
            ],
        ], $template);
    }

    public function renderSidebarLinks(): string
    {
        $stub = 'generators::Menus/_sidebar.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        return $renderer->appendMultipleContent([
            [
                'search' => '{{icon}}',
                'keep_search' => false,
                'content' => $this->getSidebarIconPrefix() . $this->getIcon(false),
            ],
        ], $template);
    }

    /**
     * Render the table
     */
    public function renderTable(): string
    {
        $stub = 'generators::views/model/_table.blade.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());
        $columns = $this->renderTableTitles();
        $column_count = $this->fieldsCount();

        if (! $this->isNameFieldIncludedInColumns()) {
            $column_count++;
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => '{{nameField}}',
                'keep_search' => false,
                'content' => $this->getNameField(),
            ],
            [
                'search' => '{{nameLabel}}',
                'keep_search' => false,
                'content' => $this->getNameLabel(),
            ],
            [
                'search' => '{{columnCount}}',
                'keep_search' => false,
                'content' => $column_count,
            ],
            [
                'search' => $renderer->addIndentation("// titles\n", 2),
                'keep_search' => false,
                'content' => $columns,
            ],
        ], $template);

        return $template;
    }

    /**
     * Render the table rows
     */
    public function renderTableRows(): string
    {
        $stub = 'generators::views/model' . ($this->hasSoftDeletes() ? '-soft-deletes' : '') . '/_list.blade.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());
        $columns = $this->renderTableColumns();

        $template = $renderer->appendMultipleContent([
            [
                'search' => '{{keyName}}',
                'keep_search' => false,
                'content' => $this->getKeyName(),
            ],
            [
                'search' => '{{nameLabel}}',
                'keep_search' => false,
                'content' => $this->getNameLabel(),
            ],
            [
                'search' => "</x-forms::table.cell>\n",
                'keep_search' => true,
                'content' => $columns ? "\n" . $columns : '',
            ],
        ], $template);

        return $template;
    }

    public function renderFilters(): string
    {
        $stub = 'generators::views/model' . ($this->hasSoftDeletes() ? '-soft-deletes' : '') . '/_filter.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderActions(): string
    {
        $stub = 'generators::views/model' . ($this->hasSoftDeletes() ? '-soft-deletes' : '') . '/_actions.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderBulkActions(): string
    {
        $stub = 'generators::views/model/_bulk.blade.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    /**
     * Get the blade code for the column
     */
    public function getFormComponentBlade(string $column): ?string
    {
        $field = $this->getField($column);

        if (! $field) {
            return null;
        }

        return $field->renderFormComponent();
    }

    /**
     * Get the blade code for the column
     */
    public function getEntryComponentBlade(string $column): ?string
    {
        $field = $this->getField($column);

        if (! $field) {
            return null;
        }

        return $field->renderEntryComponent();
    }

    /**
     * Get the blade code for the column
     */
    public function getTableCellComponentBlade(string $column): ?string
    {
        $field = $this->getField($column);

        if (! $field) {
            return null;
        }

        return $field->renderTableCellComponent();
    }

    /**
     * Get the blade code for the column
     */
    public function getTableTitleComponentBlade(string $column): ?string
    {
        $field = $this->getField($column);

        if (! $field) {
            return null;
        }

        return $field->renderTableTitleComponent();
    }
}
