<?php

namespace Javaabu\Generators\Generators\Auth;

use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Generators\Generator;
use Javaabu\Generators\Generators\RoutesGenerator;
use Javaabu\Generators\Support\PhpFile;

/**
 * The user type's auth routes (login, registration, password resets, account
 * pages) in routes/web.php, and the admins' account actions on its users.
 */
class AuthRoutesGenerator extends Generator
{
    public function files(): array
    {
        $type = (string) $this->resource->user_type;

        $web = new GeneratedFile(base_path('routes/web.php'), $this->renderAuthRoutes(), function (string $code) use ($type) {
            if (str_contains($code, "Route::authRoutes('{$type}'")) {
                return $code;
            }

            if (trim($code) === '') {
                $code = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n";
            }

            return rtrim($code) . "\n\n" . $this->renderAuthRoutes() . "\n";
        }, 'auth routes');

        $routes = new RoutesGenerator($this->resource);

        $admin = new GeneratedFile($routes->routesPath(), $this->renderAccountActions(), function (string $code) use ($routes) {
            if (trim($code) === '') {
                $code = $routes->newRoutesFile();
            }

            if (! str_contains($code, RoutesGenerator::MARKER)) {
                return rtrim($code) . "\n\n" . $this->renderAccountActions() . "\n";
            }

            return PhpFile::insertBeforeMarker($code, RoutesGenerator::MARKER, $this->renderAccountActions());
        }, 'account actions');

        return [$web, $admin];
    }

    public function prefix(): string
    {
        return StringCaser::kebab((string) $this->resource->user_type);
    }

    public function renderAuthRoutes(): string
    {
        $type = (string) $this->resource->user_type;

        return "// Login, registration, password resets and account pages of {$this->resource->pluralLower()} (javaabu/auth)\n"
            . "Route::authRoutes('{$type}', ['prefix' => '{$this->prefix()}']);";
    }

    public function renderAccountActions(): string
    {
        $type = (string) $this->resource->user_type;

        return "// Unlock, reset two-factor, require a new password and log out {$this->resource->pluralLower()}\n"
            . "Route::userAccountActions('{$type}', ['prefix' => '{$this->resource->kebab()}']);";
    }
}
