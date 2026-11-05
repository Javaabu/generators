<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Fields\StringField;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * Feature tests for the generated controller: listing, searching, creating,
 * validating, showing, updating, deleting, restoring, bulk actions and
 * authorization. They run in the app as they are.
 */
class TestGenerator extends Generator
{
    protected Imports $imports;

    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->testClass()), $this->renderTest(), description: 'tests')];
    }

    public function renderTest(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('tests');

        $this->imports = new Imports([
            'Illuminate\\Foundation\\Testing\\RefreshDatabase',
            'PHPUnit\\Framework\\Attributes\\Test',
            'Tests\\TestCase',
        ], $namespace);

        $user = $this->imports->add((string) $resource->area('user_model', 'App\\Models\\User'));
        $this->imports->add($resource->modelClass());

        $tests = array_filter([
            $this->listTest(),
            $this->listForbiddenTest(),
            $this->searchTest(),
            $this->createPageTest(),
            $this->storeTest(),
            $this->validationTest(),
            $this->storeForbiddenTest(),
            $this->showTest(),
            $this->editPageTest(),
            $this->updateTest(),
            $this->updateForbiddenTest(),
            $this->destroyTest(),
            ...($resource->hasSoftDeletes() ? [$this->restoreTest(), $this->forceDeleteTest()] : []),
            $this->bulkDeleteTest(),
        ]);

        $guard = $resource->guard() ? ", '{$resource->guard()}'" : '';

        return Stub::make('test/ControllerTest.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->testClass()),
            'tests' => implode("\n\n", $tests),
            'user' => $user,
            'guard' => $guard,
            'imports' => $this->imports->render(),
        ])->render();
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    protected function listTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('view')}');\n\n"
            . "\$this->get({$resource->route('index')})\n"
            . "    ->assertOk()\n"
            . $this->assertPage('Index', "has('" . Str::camel($resource->pluralVariable()) . ".data', 1)", $this->seeName($variable));

        return $this->test("it_lists_the_{$resource->plural()}", $body);
    }

    protected function listForbiddenTest(): string
    {
        $resource = $this->resource;

        $body = $this->parentSetup()
            . "\$this->actingAsAdmin();\n\n"
            . "\$this->get({$resource->route('index')})\n"
            . '    ->assertForbidden();';

        return $this->test("it_does_not_list_the_{$resource->plural()}_without_permission", $body);
    }

    protected function searchTest(): ?string
    {
        $resource = $this->resource;
        $name = $resource->nameField();
        $field = $resource->field($name);

        if (! $field instanceof StringField || $field->getKind() !== 'text' || $field->getMax() < 20) {
            return null;
        }

        if (! in_array($name, $resource->searchableColumns(), true)) {
            return null;
        }

        $variable = '$' . $resource->variable();
        $plural = Str::camel($resource->pluralVariable());

        $route = $resource->isNested()
            ? "route('{$resource->routeName('index')}', [\${$resource->parentVariable()}, 'search' => 'ރަސްމީ'])"
            : "route('{$resource->routeName('index')}', ['search' => 'ރަސްމީ'])";

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create(['{$name}' => 'ރަސްމީ ދުވަސް']);\n"
            . "\$other = {$this->factory()}->create(['{$name}' => 'Something else']);\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('view')}');\n\n"
            . "\$this->get({$route})\n"
            . "    ->assertOk()\n"
            . ($resource->isInertia()
                ? $this->assertPage('Index', "has('{$plural}.data', 1)\n        ->where('{$plural}.data.0.{$resource->key_name}', {$variable}->{$resource->key_name})", null)
                : "    ->assertSee('ރަސްމީ ދުވަސް')\n    ->assertDontSee('Something else');");

        return $this->test("it_searches_the_{$resource->plural()}", $body);
    }

    protected function createPageTest(): string
    {
        $resource = $this->resource;

        $body = $this->parentSetup()
            . "\$this->actingAsAdmin('{$resource->permission('edit')}');\n\n"
            . "\$this->get({$resource->route('create')})\n"
            . "    ->assertOk()\n"
            . $this->assertPage('Create', "has('form')", "    ->assertSee(__('New {$this->escape($resource->singularLower())}'));");

        return $this->test("it_shows_the_new_{$resource->singular()}_form", $body);
    }

    protected function storeTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        [$setup, $inputs, $database, $assertions] = $this->inputs('valid');

        $parent = $resource->isNested()
            ? "\n    '{$resource->parent->getName()}' => \${$resource->parentVariable()}->{$resource->parentKey()},"
            : '';

        $body = $this->parentSetup()
            . $setup
            . "\$this->actingAsAdmin('{$resource->permission('edit')}');\n\n"
            . "\$this->post({$resource->route('store')}, [\n{$inputs}\n])\n"
            . "    ->assertRedirect()\n"
            . "    ->assertSessionHasNoErrors();\n\n"
            . "\$this->assertDatabaseHas('{$resource->table()}', [{$parent}\n{$database}\n]);";

        if ($assertions) {
            $body .= "\n\n{$variable} = {$this->model()}::query()->latest('{$resource->key_name}')->first();\n\n{$assertions}";
        }

        return $this->test("it_creates_a_{$resource->singular()}", $body);
    }

    protected function validationTest(): ?string
    {
        $resource = $this->resource;

        $inputs = [];
        $errors = [];

        foreach ($resource->formFields() as $column => $field) {
            if (in_array($column, $resource->translatable, true)) {
                continue;
            }

            $invalid = $field->getInvalidValue();

            if ($invalid === null) {
                continue;
            }

            $inputs[] = "    '{$field->getInputName()}' => {$invalid},";
            $errors[] = "'{$field->getInputName()}'";
        }

        if (! $errors) {
            return null;
        }

        $body = $this->parentSetup()
            . "\$this->actingAsAdmin('{$resource->permission('edit')}');\n\n"
            . "\$this->post({$resource->route('store')}, [\n" . implode("\n", $inputs) . "\n])\n"
            . '    ->assertSessionHasErrors([' . implode(', ', $errors) . "]);\n\n"
            . "\$this->assertDatabaseCount('{$resource->table()}', 0);";

        return $this->test("it_validates_new_{$resource->plural()}", $body);
    }

    protected function storeForbiddenTest(): string
    {
        $resource = $this->resource;

        $body = $this->parentSetup()
            . "\$this->actingAsAdmin('{$resource->permission('view')}');\n\n"
            . "\$this->post({$resource->route('store')}, [])\n"
            . "    ->assertForbidden();\n\n"
            . "\$this->assertDatabaseCount('{$resource->table()}', 0);";

        return $this->test("it_does_not_create_{$resource->plural()}_without_permission", $body);
    }

    protected function showTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('view')}');\n\n"
            . "\$this->get({$resource->route('show', $variable)})\n"
            . "    ->assertOk()\n"
            . $this->assertPage('Show', "where('" . Str::camel($resource->variable()) . ".{$resource->key_name}', {$variable}->{$resource->key_name})", $this->seeName($variable));

        return $this->test("it_shows_a_{$resource->singular()}", $body);
    }

    protected function editPageTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('edit')}');\n\n"
            . "\$this->get({$resource->route('edit', $variable)})\n"
            . "    ->assertOk()\n"
            . $this->assertPage('Edit', "has('form')", "    ->assertSee(__('Edit {$this->escape($resource->singularLower())}'));");

        return $this->test("it_shows_the_edit_{$resource->singular()}_form", $body);
    }

    protected function updateTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        [$setup, $inputs, $database, $assertions] = $this->inputs('other');

        $body = $this->parentSetup()
            . $setup
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('edit')}');\n\n"
            . "\$this->patch({$resource->route('update', $variable)}, [\n{$inputs}\n])\n"
            . "    ->assertRedirect()\n"
            . "    ->assertSessionHasNoErrors();\n\n"
            . "\$this->assertDatabaseHas('{$resource->table()}', [\n    '{$resource->key_name}' => {$variable}->{$resource->key_name},\n{$database}\n]);";

        if ($assertions) {
            $body .= "\n\n{$variable}->refresh();\n\n{$assertions}";
        }

        return $this->test("it_updates_a_{$resource->singular()}", $body);
    }

    protected function updateForbiddenTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('view')}');\n\n"
            . "\$this->patch({$resource->route('update', $variable)}, [])\n"
            . '    ->assertForbidden();';

        return $this->test("it_does_not_update_{$resource->plural()}_without_permission", $body);
    }

    protected function destroyTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $assert = $resource->hasSoftDeletes()
            ? "\$this->assertSoftDeleted({$variable});"
            : "\$this->assertModelMissing({$variable});";

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('delete')}');\n\n"
            . "\$this->delete({$resource->route('destroy', $variable)})\n"
            . "    ->assertRedirect({$resource->route('index')});\n\n"
            . $assert;

        return $this->test("it_deletes_a_{$resource->singular()}", $body);
    }

    protected function restoreTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n"
            . "{$variable}->delete();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('delete')}');\n\n"
            . "\$this->patch({$resource->route('restore', $variable)})\n"
            . "    ->assertRedirect();\n\n"
            . "\$this->assertNotSoftDeleted({$variable});";

        return $this->test("it_restores_a_deleted_{$resource->singular()}", $body);
    }

    protected function forceDeleteTest(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $body = $this->parentSetup()
            . "{$variable} = {$this->factory()}->create();\n"
            . "{$variable}->delete();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('force_delete')}');\n\n"
            . "\$this->delete({$resource->route('force-delete', $variable)})\n"
            . "    ->assertRedirect({$resource->route('index')});\n\n"
            . "\$this->assertModelMissing({$variable});";

        return $this->test("it_permanently_deletes_a_{$resource->singular()}", $body);
    }

    protected function bulkDeleteTest(): string
    {
        $resource = $this->resource;
        $plural = '$' . $resource->pluralVariable();

        $assert = $resource->hasSoftDeletes()
            ? "{$plural}->each(fn ({$this->model()} \$record) => \$this->assertSoftDeleted(\$record));"
            : "{$plural}->each(fn ({$this->model()} \$record) => \$this->assertModelMissing(\$record));";

        $body = $this->parentSetup()
            . "{$plural} = {$this->factory()}->count(2)->create();\n\n"
            . "\$this->actingAsAdmin('{$resource->permission('view')}', '{$resource->permission('delete')}');\n\n"
            . "\$this->postJson({$resource->route('bulk')}, [\n"
            . "    'action' => 'delete',\n"
            . "    '{$resource->pluralVariable()}' => {$plural}->pluck('{$resource->key_name}')->all(),\n"
            . "])\n"
            . "    ->assertOk()\n"
            . "    ->assertJson(['succeeded' => 2]);\n\n"
            . $assert;

        return $this->test("it_deletes_{$resource->plural()}_in_bulk", $body);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * The setup, inputs, database values and other assertions for a request
     * with the valid (or the other valid) values.
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    protected function inputs(string $set): array
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $setup = [];
        $inputs = [];
        $database = [];
        $assertions = [];

        /** @var Field $field */
        foreach ($resource->formFields() as $column => $field) {
            foreach ($field->getTestSetup($this->imports, '') as $line) {
                $setup[] = $line;
            }

            $value = $set === 'valid' ? $field->getValidValue() : $field->getOtherValidValue();
            $name = $field->getInputName();

            $inputs[] = "    '{$name}' => {$value},";

            $database_value = $field->getDatabaseValue($value);

            if ($database_value !== null) {
                $database[] = "    '{$column}' => {$database_value},";

                continue;
            }

            if ($assertion = $field->getSavedValueAssertion($variable, $value)) {
                $assertions[] = $assertion;
            }
        }

        foreach ($resource->formRelations() as $relation) {
            $related = $this->imports->add($resource->namespace('models') . '\\' . $relation->relatedModel());
            $items = '$' . Str::snake($relation->name);

            $setup[] = "{$items} = {$related}::factory()->count(2)->create();";

            $ids = $set === 'valid' ? "{$items}->pluck('id')->all()" : "[{$items}->first()->id]";

            $inputs[] = "    '{$relation->name}' => {$ids},";
            $assertions[] = "\$this->assertEquals({$ids}, {$variable}->{$relation->name}()->pluck('{$relation->related_table}.id')->sort()->values()->all());";
        }

        $setup = array_values(array_unique($setup));

        return [
            $setup ? implode("\n", $setup) . "\n\n" : '',
            implode("\n", $inputs),
            implode("\n", $database),
            implode("\n", $assertions),
        ];
    }

    protected function parentSetup(): string
    {
        $resource = $this->resource;

        if (! $resource->isNested()) {
            return '';
        }

        $parent = $this->imports->add($resource->parentModelClass());

        return "\${$resource->parentVariable()} = {$parent}::factory()->create();\n";
    }

    /**
     * Product::factory(), or Product::factory()->for($category, 'category') when nested.
     */
    protected function factory(): string
    {
        $resource = $this->resource;
        $factory = "{$this->model()}::factory()";

        if ($resource->isNested()) {
            $factory .= "->for(\${$resource->parentVariable()}, '{$resource->parent->getRelationName()}')";
        }

        return $factory;
    }

    protected function model(): string
    {
        return $this->imports->add($this->resource->modelClass());
    }

    protected function seeName(string $variable): string
    {
        $resource = $this->resource;

        if (! $resource->field($resource->nameField())) {
            return '    ;';
        }

        return "    ->assertSee({$variable}->{$resource->nameField()});";
    }

    /**
     * The assertion on the response: the Inertia page, or the Blade view.
     */
    protected function assertPage(string $page, string $inertia, ?string $blade): string
    {
        $resource = $this->resource;

        if (! $resource->isInertia()) {
            return $blade ?? '    ;';
        }

        $assert = $this->imports->add('Inertia\\Testing\\AssertableInertia');

        return "    ->assertInertia(fn ({$assert} \$page) => \$page\n"
            . "        ->component('{$resource->pageName($page)}')\n"
            . "        ->{$inertia}\n"
            . '    );';
    }

    protected function test(string $name, string $body): string
    {
        $body = Stub::indent(str_replace("\n    ;", ';', $body));

        return "#[Test]\npublic function {$name}(): void\n{\n{$body}\n}";
    }

    protected function escape(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }
}
