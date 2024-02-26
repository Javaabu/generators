<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\FieldTypes\Field;

class ViewsGenerator extends BaseGenerator
{
    /**
     * Render the views
     */
    public function render(): string
    {

        return '';

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
}
