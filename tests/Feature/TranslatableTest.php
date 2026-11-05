<?php

namespace Javaabu\Generators\Tests\Feature;

use InvalidArgumentException;
use Javaabu\Generators\Generators\FormGenerator;
use Javaabu\Generators\Generators\ModelGenerator;
use Javaabu\Generators\Generators\TestGenerator;
use Javaabu\Generators\Resource\ResourceOptions;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Support\Translations;
use Javaabu\Generators\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TranslatableTest extends TestCase
{
    protected function tearDown(): void
    {
        Translations::fake(null);

        parent::tearDown();
    }

    protected function resolve(string $table, array $options = [])
    {
        return app(ResourceResolver::class)->resolve($table, ResourceOptions::fromArray($options));
    }

    #[Test]
    public function it_makes_the_text_columns_translatable_when_the_package_is_installed(): void
    {
        Translations::fake(true);

        $resource = $this->resolve('pages');

        // unique columns such as slugs aren't translated
        $this->assertEquals(['title', 'body'], $resource->translatable);

        $model = (new ModelGenerator($resource))->renderModel();

        $this->assertStringContainsString('class Page extends Model implements JsonTranslatable', $model);
        $this->assertStringContainsString('use IsJsonTranslatable;', $model);
        $this->assertStringContainsString("protected array \$translatable = ['title', 'body'];", $model);

        $form = (new FormGenerator($resource))->renderForm();

        $this->assertStringContainsString("TextInput::make('title')\n                        ->label(__('Title'))\n                        ->required()\n                        ->maxLength(255)\n                        ->translatable(),", $form);

        $test = (new TestGenerator($resource))->renderTest();

        $this->assertStringContainsString('use InteractsWithLanguages;', $test);
        $this->assertStringContainsString("'title_dv' => 'ދިވެހި title',", $test);
    }

    #[Test]
    public function it_writes_plain_models_without_the_package(): void
    {
        Translations::fake(false);

        $resource = $this->resolve('pages');

        $this->assertEquals([], $resource->translatable);
        $this->assertArrayNotHasKey('translations', $resource->fields);
        $this->assertArrayNotHasKey('lang', $resource->fields);

        $this->assertStringNotContainsString('Translatable', (new ModelGenerator($resource))->renderModel());
        $this->assertStringNotContainsString('translatable()', (new FormGenerator($resource))->renderForm());
        $this->assertStringNotContainsString('InteractsWithLanguages', (new TestGenerator($resource))->renderTest());
    }

    #[Test]
    public function it_skips_tables_without_the_translation_columns(): void
    {
        Translations::fake(true);

        $this->assertEquals([], $this->resolve('posts')->translatable);
    }

    #[Test]
    public function it_takes_the_translatable_columns_from_the_option(): void
    {
        Translations::fake(true);

        $this->assertEquals(['title'], $this->resolve('pages', ['translatable' => 'title'])->translatable);
    }

    #[Test]
    public function it_can_be_turned_off_in_config(): void
    {
        Translations::fake(true);

        config()->set('generators.translatable', false);

        $this->assertEquals([], $this->resolve('pages')->translatable);
    }

    #[Test]
    public function it_explains_the_missing_columns(): void
    {
        Translations::fake(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$table->json('translations')->nullable();");

        $this->resolve('posts', ['translatable' => 'title']);
    }
}
