<?php

namespace Javaabu\Generators\Generators\Auth;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Generators\BaseGenerator;
use Javaabu\Generators\Generators\Concerns\GeneratesFactory;
use Javaabu\Generators\Generators\Concerns\GeneratesPermissions;
use Javaabu\GeneratorHelpers\StringCaser;

class AuthPermissionsGenerator extends BaseAuthGenerator
{
    use GeneratesPermissions;

    protected string $permissions_stub = 'generators::seeders/_permissionsSoftDeletes.stub';
    protected string $additional_permissions_stub = 'generators::seeders/_authPermissions.stub';

    /**
     * Render the views
     */
    public function render(): string
    {
        return $this->renderPermissions();
    }
}
