<?php

namespace Javaabu\Generators\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Javaabu\Generators\Contracts\SchemaResolverInterface;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\DateField;
use Javaabu\Generators\FieldTypes\DateTimeField;
use Javaabu\Generators\FieldTypes\DecimalField;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\FieldTypes\IntegerField;
use Javaabu\Generators\FieldTypes\JsonField;
use Javaabu\Generators\FieldTypes\StringField;
use Javaabu\Generators\FieldTypes\TextField;
use Javaabu\Generators\FieldTypes\TimeField;
use Javaabu\Generators\FieldTypes\YearField;
use stdClass;

class SchemaResolverMySql extends BaseSchemaResolver implements SchemaResolverInterface
{
    public static array $integerTypes = [
        'tinyint' => [
            'unsigned' => ['0', '255'],
            'signed' => ['-128', '127'],
        ],
        'smallint' => [
            'unsigned' => ['0', '65535'],
            'signed' => ['-32768', '32767'],
        ],
        'mediumint' => [
            'unsigned' => ['0', '16777215'],
            'signed' => ['-8388608', '8388607'],
        ],
        'int' => [
            'unsigned' => ['0', '4294967295'],
            'signed' => ['-2147483648', '2147483647'],
        ],
        'bigint' => [
            'unsigned' => ['0', '18446744073709551615'],
            'signed' => ['-9223372036854775808', '9223372036854775807'],
        ],
    ];

    public static array $textTypes = [
        'tinytext' => 255,
        'text' => 65535,
        'mediumtext' => 16777215,
        'longtext' => 4294967295,
    ];

    protected function getColumnsDefinitionsFromTable()
    {
        $databaseName = config('database.connections.mysql.database');
        $tableName = $this->table();

        $tableColumns = collect(DB::select('SHOW COLUMNS FROM `'.$tableName.'`'))->keyBy('Field')->toArray();

        $foreignKeys = DB::select("
            SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
            FROM information_schema.TABLE_CONSTRAINTS i
            LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
            WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND i.TABLE_SCHEMA = '{$databaseName}'
            AND i.TABLE_NAME = '{$tableName}'
        ");

        foreach ($foreignKeys as $foreignKey) {
            $tableColumns[$foreignKey->COLUMN_NAME]->Foreign = [
                'table' => $foreignKey->REFERENCED_TABLE_NAME,
                'id' => $foreignKey->REFERENCED_COLUMN_NAME,
            ];
        }

        $columnComments = DB::select("
            SELECT COLUMN_NAME, COLUMN_COMMENT
            FROM information_schema.COLUMNS i
            WHERE i.TABLE_SCHEMA = '{$databaseName}'
            AND i.TABLE_NAME = '{$tableName}'
        ");

        foreach ($columnComments as $comment) {
            $tableColumns[$comment->COLUMN_NAME]->Comment = $comment->COLUMN_COMMENT;
        }

        return $tableColumns;
    }

    protected function resolveColumnFieldType(stdClass $column): ?Field
    {
        $name = $this->getField($column);

        $is_unique = $column->Key === 'UNI';
        $is_nullable = $column->Null === 'YES';
        $default = $column->Default;

        if (! empty($column->Foreign)) {
            return new ForeignKeyField(
                $name,
                $column->Foreign['table'],
                $column->Foreign['id'],
                $is_nullable,
                unique: $is_unique
            );
        }

        $type = Str::of($column->Type);

        switch (true) {
            case $type->contains('enum')
                || $type->contains('set')
                || Str::startsWith($column->Comment, 'enum:'):

                if (Str::startsWith($column->Comment, 'enum:')) {
                    $enum_class = Str::after($column->Comment, 'enum:');
                    $options = [];
                } else {
                    preg_match_all("/'([^']*)'/", $type, $matches);
                    $options = $matches[1];
                    $enum_class = null;
                }

                return new EnumField(
                    $name,
                    $options,
                    $enum_class,
                    nullable: $is_nullable,
                    default: $default,
                    unique: $is_unique
                );
                break;

            case $type == 'tinyint(1)' && config('generators.tinyint1_to_bool'):
                return new BooleanField(
                    $name,
                    $is_nullable,
                    default: $default == true,
                    unique: $is_unique
                );
                break;

            case $type->contains('char'):
                $max = filter_var($type, FILTER_SANITIZE_NUMBER_INT);

                return new StringField(
                    $name,
                    $is_nullable,
                    default: $default,
                    max: $max,
                    unique: $is_unique
                );
                break;

            case $type == 'tinytext':
            case $type == 'mediumtext':
            case $type == 'longtext':
            case $type == 'text':
                $text_type = $type->toString();

                if (! array_key_exists($text_type, self::$textTypes)) {
                    $text_type = 'text';
                }

                $max = self::$textTypes[$text_type];

                return new TextField(
                    $name,
                    $is_nullable,
                    default: $default,
                    max: $max,
                    unique: $is_unique
                );
                break;

            case $type->contains('int'):

                $unsigned = $type->contains('unsigned');

                $sign = $unsigned ? 'unsigned' : 'signed';
                $intType = $type->before(' unsigned')->__toString();

                // prevent int(xx) for mysql
                $intType = preg_replace("/\([^)]+\)/", '', $intType);

                if (! array_key_exists($intType, self::$integerTypes)) {
                    $intType = 'int';
                }

                $min = self::$integerTypes[$intType][$sign][0];
                $max = self::$integerTypes[$intType][$sign][1];

                return new IntegerField(
                    $name,
                    $is_nullable,
                    default: $default,
                    min: $min,
                    max: $max,
                    unsigned: $unsigned,
                    unique: $is_unique
                );
                break;

            case $type->contains('double') ||
            $type->contains('decimal') ||
            $type->contains('dec') ||
            $type->contains('float'):
                $unsigned = $type->contains('unsigned');
                [$total_digits, $places] = $type->between('(', ')')
                               ->explode(',');

                $total_digits = (int) $total_digits;
                $places = (int) $places;

                $whole_number_digits = ($total_digits - $places) - ($unsigned ? 0 : 1);

                $max = Str::repeat('9', $whole_number_digits);
                $min = $unsigned ? 0 : '-' . $max;

                return new DecimalField(
                    $name,
                    $total_digits,
                    $places,
                    $is_nullable,
                    default: $default,
                    min: (int) $min,
                    max: (int) $max,
                    unsigned: $unsigned,
                    unique: $is_unique
                );

                break;

            case $type->contains('year'):
                return new YearField(
                    $name,
                    $is_nullable,
                    default: $default,
                    min: 1900,
                    max: 2100,
                    unique: $is_unique
                );
                break;


            case $type == 'datetime' || $type == 'timestamp':
                return new DateTimeField(
                    $name,
                    $is_nullable,
                    default: $default,
                    unique: $is_unique
                );
                break;

            case $type == 'date':
                return new DateField(
                    $name,
                    $is_nullable,
                    default: $default,
                    unique: $is_unique
                );
                break;

            case $type == 'time':
                return new TimeField(
                    $name,
                    $is_nullable,
                    default: $default,
                    unique: $is_unique
                );
                break;

            case $type == 'json':
                return new JsonField(
                    $name,
                    $is_nullable,
                    default: $default,
                    unique: $is_unique
                );
                break;

            default:
                // I think we skip BINARY and BLOB for now
                break;
        }

        return null;
    }

    protected function isAutoIncrement($column): bool
    {
        return $column->Extra === 'auto_increment';
    }

    protected function getField($column): string
    {
        return $column->Field;
    }
}
