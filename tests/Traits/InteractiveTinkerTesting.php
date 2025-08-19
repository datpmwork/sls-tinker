<?php

namespace DatPM\SlsTinker\Tests\Traits;

use Symfony\Component\Process\Process;

trait InteractiveTinkerTesting
{
    protected function runTinkerCommands(array $commands, int $timeout = 10): string
    {
        $process = new Process(['php', 'artisan', 'tinker'], base_path());

        // Ensure we exit at the end
        if (end($commands) !== 'exit') {
            $commands[] = 'exit';
        }

        // Join all commands with newlines
        $input = implode("\n", $commands);

        $process->setInput($input);
        $process->setTimeout($timeout);

        $process->run();

        // Return both stdout and stderr combined
        return $process->getOutput().$process->getErrorOutput();
    }

    protected function expectTinkerOutput(array $commands, string $expectedOutput): void
    {
        $output = $this->runTinkerCommands($commands);
        expect($output)->toContain($expectedOutput);
    }
}
