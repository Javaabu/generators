<?php

namespace Javaabu\Generators\Schema;

final readonly class ForeignKey
{
    public function __construct(
        public string $column,
        public string $foreign_table,
        public string $foreign_column = 'id',
        public ?string $on_delete = null,
    ) {}
}
