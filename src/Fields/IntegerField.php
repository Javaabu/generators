<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class IntegerField extends Field
{
    /** @var array<string, array{0: string, 1: string, 2: string}> */
    public const array RANGES = [
        'tinyint' => ['-128', '127', '255'],
        'smallint' => ['-32768', '32767', '65535'],
        'mediumint' => ['-8388608', '8388607', '16777215'],
        'int' => ['-2147483648', '2147483647', '4294967295'],
        'bigint' => ['-9223372036854775808', '9223372036854775807', '18446744073709551615'],
    ];

    public function __construct(
        string $name,
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
        protected string $size = 'int',
        protected bool $unsigned = false,
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function getMin(): string
    {
        return $this->unsigned ? '0' : str_replace('9223372036854775808', '9223372036854775807', self::RANGES[$this->size][0]);
    }

    public function getMax(): string
    {
        $max = $this->unsigned ? self::RANGES[$this->size][2] : self::RANGES[$this->size][1];

        // keep it within PHP_INT_MAX so the rule and the faker work
        $limit = (string) PHP_INT_MAX;

        if (strlen($max) > strlen($limit) || (strlen($max) === strlen($limit) && strcmp($max, $limit) > 0)) {
            return $limit;
        }

        return $max;
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'integer'";
    }

    protected function fakerCall(): string
    {
        $max = min((int) $this->getMax(), 10000);
        $min = max((int) $this->getMin(), 0);

        return "numberBetween({$min}, {$max})";
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TextInput';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ['integer()', "minValue({$this->getMin()})", "maxValue({$this->getMax()})"];
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\NumberRangeFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})->integer()";
    }

    public function getTypeScriptType(): string
    {
        return 'number';
    }

    public function getValidValue(): string
    {
        return '10';
    }

    public function getOtherValidValue(): string
    {
        return '20';
    }

    public function getInvalidValue(): ?string
    {
        return "'ބަދަލު'";
    }
}
