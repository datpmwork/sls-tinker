<?php

namespace DatPM\SlsTinker\Tests;

class LambdaTinkerTest extends TestCase
{
    public function test_empty_context(): void
    {
        $this->expectTinkerOutput('function', [
            'echo "Laravel";',
        ], function (array $output): void {
            $this->assertOutputContainsInOrder([
                'You\'re running Tinker in AWS Lambda',
                'Target Lambda: function',
                'Laravel',
            ], $output);
        });
    }

    public function test_closure_throw_error(): void
    {
        $this->expectTinkerOutput('function', [
            '$a = fn() => 1;',
        ], function (array $output): void {
            $this->assertOutputContainsInOrder([
                'You\'re running Tinker in AWS Lambda',
                'Target Lambda: function',
                "Exception  Serialization of 'Closure' is not allowed.",
            ], $output);
        });
    }

    public function test_variable_persistence_across_commands(): void
    {
        $this->expectTinkerOutput('function', [
            '$name = "Laravel";',
            '$version = "10";',
            'echo $name . " " . $version;',
            '$a = 1;',
            '$b = 2;',
            '$c = $a + $b;',
            'echo $c;',
            '$c = 10;',
            'echo $c;',
        ], function (array $output): void {
            $this->assertOutputContainsInOrder([
                'You\'re running Tinker in AWS Lambda',
                'Target Lambda: function',
                '<whisper>= </whisper>"Laravel"',
                '<whisper>= </whisper>"10"',
                'Laravel 10',
                '<whisper>= </whisper>1',
                '<whisper>= </whisper>2',
                '<whisper>= </whisper>3',
                '3',
                '<whisper>= </whisper>10',
                '10',
            ], $output);
        });
    }

    public function test_wrong_variable_usage_across_commands(): void
    {
        $this->expectTinkerOutput('function', [
            '$a = 1;',
            'echo $c;',
            'echo "a = $a";',
        ], function (array $output): void {
            $outputText = implode("\n", $output);

            $this->assertStringContainsString('Undefined variable $c.', $outputText);
            $this->assertStringContainsString('a = 1', $outputText);
        });
    }

    public function test_should_fail_when_lambda_function_not_found(): void
    {
        $this->expectTinkerOutput('wrong-function', [
            '$name = "Laravel";',
            '$version = "10";',
            'echo $name . " " . $version;',
            '$a = 1;',
            '$b = 2;',
            '$c = $a + $b;',
            'echo $c;',
        ], function (array $output): void {
            $this->assertStringContainsString('HTTP 404 returned', implode("\n", $output));
        });
    }

    private function assertOutputContainsInOrder(array $expectedOutput, array $actualOutput): void
    {
        $offset = 0;

        foreach ($expectedOutput as $expectedLine) {
            $foundAt = array_search($expectedLine, array_slice($actualOutput, $offset), true);

            $this->assertNotFalse(
                $foundAt,
                sprintf(
                    "Failed asserting that output contains [%s] after offset %d.\n\nActual output:\n%s",
                    $expectedLine,
                    $offset,
                    implode("\n", $actualOutput),
                ),
            );

            $offset += $foundAt + 1;
        }
    }
}
