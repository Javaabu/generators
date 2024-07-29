<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Tests\TestCase;

class StringCaserTest extends TestCase
{
    /** @test */
    public function it_can_generate_correct_string_cases_in_studly_format(): void
    {
        $name = 'FormInputField';

        $this->assertEquals('formInputFields', StringCaser::pluralCamel($name));
        $this->assertEquals('form-input-fields', StringCaser::pluralKebab($name));
        $this->assertEquals('form_input_fields', StringCaser::pluralSnake($name));
        $this->assertEquals('FormInputFields', StringCaser::pluralStudly($name));
        $this->assertEquals('Form Input Fields', StringCaser::pluralTitle($name));
        $this->assertEquals('form input fields', StringCaser::pluralLower($name));

        $this->assertEquals('formInputField', StringCaser::singularCamel($name));
        $this->assertEquals('form-input-field', StringCaser::singularKebab($name));
        $this->assertEquals('form_input_field', StringCaser::singularSnake($name));
        $this->assertEquals('FormInputField', StringCaser::singularStudly($name));
        $this->assertEquals('Form Input Field', StringCaser::singularTitle($name));
        $this->assertEquals('form input field', StringCaser::singularLower($name));

        $this->assertEquals('formInputField', StringCaser::camel($name));
        $this->assertEquals('form-input-field', StringCaser::kebab($name));
        $this->assertEquals('form_input_field', StringCaser::snake($name));
        $this->assertEquals('FormInputField', StringCaser::studly($name));
        $this->assertEquals('Form Input Field', StringCaser::title($name));
        $this->assertEquals('form input field', StringCaser::lower($name));
    }

    /** @test */
    public function it_can_generate_correct_string_cases_in_snake_format(): void
    {
        $name = 'Form_input_Field';

        $this->assertEquals('formInputFields', StringCaser::pluralCamel($name));
        $this->assertEquals('form-input-fields', StringCaser::pluralKebab($name));
        $this->assertEquals('form_input_fields', StringCaser::pluralSnake($name));
        $this->assertEquals('FormInputFields', StringCaser::pluralStudly($name));
        $this->assertEquals('Form Input Fields', StringCaser::pluralTitle($name));
        $this->assertEquals('form input fields', StringCaser::pluralLower($name));

        $this->assertEquals('formInputField', StringCaser::singularCamel($name));
        $this->assertEquals('form-input-field', StringCaser::singularKebab($name));
        $this->assertEquals('form_input_field', StringCaser::singularSnake($name));
        $this->assertEquals('FormInputField', StringCaser::singularStudly($name));
        $this->assertEquals('Form Input Field', StringCaser::singularTitle($name));
        $this->assertEquals('form input field', StringCaser::singularLower($name));

        $this->assertEquals('formInputField', StringCaser::camel($name));
        $this->assertEquals('form-input-field', StringCaser::kebab($name));
        $this->assertEquals('form_input_field', StringCaser::snake($name));
        $this->assertEquals('FormInputField', StringCaser::studly($name));
        $this->assertEquals('Form Input Field', StringCaser::title($name));
        $this->assertEquals('form input field', StringCaser::lower($name));
    }

    /** @test */
    public function it_can_generate_correct_string_cases_in_kebab_format(): void
    {
        $name = 'form-input-field';

        $this->assertEquals('formInputFields', StringCaser::pluralCamel($name));
        $this->assertEquals('form-input-fields', StringCaser::pluralKebab($name));
        $this->assertEquals('form_input_fields', StringCaser::pluralSnake($name));
        $this->assertEquals('FormInputFields', StringCaser::pluralStudly($name));
        $this->assertEquals('Form Input Fields', StringCaser::pluralTitle($name));
        $this->assertEquals('form input fields', StringCaser::pluralLower($name));

        $this->assertEquals('formInputField', StringCaser::singularCamel($name));
        $this->assertEquals('form-input-field', StringCaser::singularKebab($name));
        $this->assertEquals('form_input_field', StringCaser::singularSnake($name));
        $this->assertEquals('FormInputField', StringCaser::singularStudly($name));
        $this->assertEquals('Form Input Field', StringCaser::singularTitle($name));
        $this->assertEquals('form input field', StringCaser::singularLower($name));

        $this->assertEquals('formInputField', StringCaser::camel($name));
        $this->assertEquals('form-input-field', StringCaser::kebab($name));
        $this->assertEquals('form_input_field', StringCaser::snake($name));
        $this->assertEquals('FormInputField', StringCaser::studly($name));
        $this->assertEquals('Form Input Field', StringCaser::title($name));
        $this->assertEquals('form input field', StringCaser::lower($name));
    }


}
