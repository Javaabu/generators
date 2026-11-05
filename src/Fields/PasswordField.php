<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

/**
 * The password of a user type: required for new users, kept when left empty.
 * Never shown or sent to the browser.
 */
class PasswordField extends Field
{
    public function isIndexColumn(): bool
    {
        return false;
    }

    public function isShown(): bool
    {
        return false;
    }

    public function getFactoryValue(Imports $imports): string
    {
        return "'password'";
    }

    protected function fakerCall(): string
    {
        return 'password()';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TextInput';
    }

    public function getStencilField(Imports $imports, array $extra = []): string
    {
        $class = $imports->add($this->stencilClass());

        return "{$class}::make('{$this->getName()}')\n"
            . "    ->label({$this->getLabelCall()})\n"
            . "    ->password()\n"
            . "    ->required(fn (string \$operation) => \$operation === 'create')\n"
            . "    ->minLength(8)\n"
            . "    ->maxLength(255)\n"
            . '    // left empty when editing: the password stays' . "\n"
            . '    ->dehydrated(fn (?string $state) => filled($state))';
    }

    public function getValidValue(): string
    {
        return "'Ab12cd34ef!'";
    }

    public function getOtherValidValue(): string
    {
        return "'Zy98xw76vu!'";
    }

    public function getInvalidValue(): ?string
    {
        return "'short'";
    }

    public function getDatabaseValue(string $input): ?string
    {
        return null;
    }

    public function getSavedValueAssertion(string $variable, string $input, ?Imports $imports = null): ?string
    {
        $hash = $imports ? $imports->add('Illuminate\\Support\\Facades\\Hash') : '\\Illuminate\\Support\\Facades\\Hash';

        return "\$this->assertTrue({$hash}::check({$input}, {$variable}->{$this->getName()}));";
    }
}
