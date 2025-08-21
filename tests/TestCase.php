<?php

namespace DatPM\SlsTinker\Tests;

use DatPM\SlsTinker\SlsTinkerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Symfony\Component\Process\Process;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SlsTinkerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function runTinkerCommands(array $commands, string $lambdaFunction = 'function', int $timeout = 0): string
    {
        $process = new Process(['php', 'artisan', 'sls-tinker', $lambdaFunction], base_path());

        $process->setPty(true);

        // Ensure we exit at the end
        if (end($commands) !== 'exit;') {
            $commands[] = 'exit;';
        }

        // Join all commands with newlines
        $input = implode("\n", $commands)."\n";

        $process->setInput($input);
        $process->setTimeout($timeout);

        $process->run();

        // Return both stdout and stderr combined
        return $process->getOutput().$process->getErrorOutput();
    }

    protected function extractEchoOutput(string $fullOutput): array
    {
        // Strip ANSI color codes
        $clean = preg_replace('/\e[\[\]()#;?0-9]*[a-zA-Z=]/', '', $fullOutput);

        // Split into lines
        $lines = explode("\n", $clean);

        $resultLines = [];
        $insidePsyShell = false;

        foreach ($lines as $line) {
            $trimmed = trim($line, " \n\r\t\v\0\e");

            // Wait until Psy Shell starts
            if (! $insidePsyShell) {
                if (str_contains($trimmed, 'Psy Shell')) {
                    $insidePsyShell = true;
                }

                continue;
            }

            // Filter out prompt/response/exit lines
            if ($trimmed === ''
                || str_starts_with($trimmed, '>')
                || str_starts_with($trimmed, 'INFO  Goodbye.')) {
                continue;
            }

            // Capture everything else
            $resultLines[] = $trimmed;
        }

        return $resultLines;
    }

    protected function expectTinkerOutput(string $lambdaFunction, array $commands, $expect): void
    {
        $output = $this->extractEchoOutput($this->runTinkerCommands($commands, $lambdaFunction));
        $expect($output);
    }
}
