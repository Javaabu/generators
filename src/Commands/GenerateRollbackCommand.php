<?php

namespace Javaabu\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateRollbackCommand extends Command
{
    use ConfirmableTrait;

    protected $name = 'generate:rollback';

    protected $description = 'Roll back the changes of a generate command (discards every uncommitted change in git)';

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Run the command without asking'],
        ];
    }

    public function handle(): int
    {
        if (! $this->confirmToProceed(__('generators::generators.rollback_warning'), true)) {
            return self::FAILURE;
        }

        $commands = [
            ['git', 'reset'],
            ['git', 'checkout', '.'],
            ['git', 'clean', '-fd'],
        ];

        foreach ($commands as $arguments) {
            $process = new Process($arguments, base_path());
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->line($process->getOutput());
        }

        $this->components->info(__('generators::generators.rolled_back'));

        return self::SUCCESS;
    }
}
