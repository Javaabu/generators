<?php

namespace Javaabu\Generators\Fields;

use Illuminate\Support\Str;
use Javaabu\Generators\Support\Imports;

class StringField extends Field
{
    public function __construct(
        string $name,
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
        protected int $max = 255,
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * email, tel, url or text, from the column name.
     */
    public function getKind(): string
    {
        $name = $this->getName();

        if ($name === 'email' || Str::endsWith($name, '_email')) {
            return 'email';
        }

        if (Str::contains($name, ['phone', 'mobile']) || $name === 'tel') {
            return 'tel';
        }

        if (in_array($name, ['url', 'website', 'link'], true) || Str::endsWith($name, ['_url', '_website'])) {
            return 'url';
        }

        return 'text';
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function isSortable(): bool
    {
        return true;
    }

    protected function fakerCall(): string
    {
        $max = $this->getMax();

        return match ($this->getKind()) {
            'email' => 'safeEmail()',
            'tel' => "numerify('7######')",
            'url' => 'url()',
            default => $max < 5 ? "regexify('[A-Z]{{$max}}')" : 'text(' . min($max, 50) . ')',
        };
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TextInput';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        $modifiers = [];

        $kind = $this->getKind();

        if ($kind !== 'text') {
            $modifiers[] = "{$kind}()";
        }

        $modifiers[] = "maxLength({$this->getMax()})";

        if ($this->isUnique()) {
            $modifiers[] = 'unique()';
        }

        return $modifiers;
    }

    public function getValidValue(): string
    {
        return match ($this->getKind()) {
            'email' => "'ali@example.com'",
            'tel' => "'7771234'",
            'url' => "'https://example.com'",
            default => $this->getMax() < 5 ? "'" . substr('ABCD', 0, $this->getMax()) . "'" : "'ރަސްމީ'",
        };
    }

    public function getOtherValidValue(): string
    {
        return match ($this->getKind()) {
            'email' => "'aisha@example.com'",
            'tel' => "'9601234'",
            'url' => "'https://example.mv'",
            default => $this->getMax() < 5 ? "'" . substr('WXYZ', 0, $this->getMax()) . "'" : "'ދިވެހި'",
        };
    }
}
