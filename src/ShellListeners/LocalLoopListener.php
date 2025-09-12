<?php

namespace DatPM\SlsTinker\ShellListeners;

use DatPM\SlsTinker\Lambda\InvocationResult;
use DatPM\SlsTinker\Lambda\TinkerLambdaClient;
use DatPM\SlsTinker\Shells\LambdaShell;
use Psy\Exception\BreakException;
use Psy\Exception\ThrowUpException;
use Psy\ExecutionClosure;
use Psy\ExecutionLoop\AbstractListener;
use Psy\Shell;

class LocalLoopListener extends AbstractListener
{
    protected $lambdaFunctionName;

    public function __construct(string $lambdaFunctionName)
    {
        $this->lambdaFunctionName = $lambdaFunctionName;
    }

    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @return array|InvocationResult
     */
    public function invokeLambdaFunction($arguments)
    {
        // Because arguments may contain spaces, and are going to be executed remotely
        // as a separate process, we need to escape all arguments.
        $arguments = array_map(static function (string $arg): string {
            return escapeshellarg($arg);
        }, $arguments);

        $lambda = new TinkerLambdaClient(
            getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            getenv('AWS_PROFILE') ?: 'default',
        );

        return $lambda->invoke($this->lambdaFunctionName, json_encode([
            'cli' => implode(' ', $arguments),
        ]));
    }

    /**
     * Evaluate the current code buffer.
     *
     * @param  LambdaShell  $shell
     *
     * @throws BreakException
     * @throws ThrowUpException
     */
    public function onExecute(Shell $shell, string $code)
    {
        if ($code == '\Psy\Exception\BreakException::exitShell();') {
            return $code;
        }

        $vars = $shell->getScopeVariables(false);
        $context = $vars['_context'] ?? base64_encode(serialize(['_' => null]));
        try {
            // Evaluate the current code buffer
            $result = $this->invokeLambdaFunction([
                'sls-tinker',
                $this->lambdaFunctionName,
                '--execute',
                $code,
                '--context',
                $context,
            ]);

            $extractedOutput = $shell->extractContextData($result->getOutput());
            if (is_null($extractedOutput)) {
                throw new BreakException('The remote tinker shell returned an invalid payload');
            }

            if ([$output, $context, $return] = $extractedOutput) {
                if (! empty($output)) {
                    $shell->getRawOutput()->writeln($output);
                }
                if (! empty($return)) {
                    $shell->getRawOutput()->writeln($return);
                }
                if (! empty($context)) {
                    // Extract _context into shell's scope variables for next code execution
                    // Return NoValue as output and return value were printed out
                    return "extract(['_context' => '{$context}']); return new \Psy\CodeCleaner\NoReturnValue();";
                } else {
                    // Return NoValue as output and return value were printed out
                    return "return new \Psy\CodeCleaner\NoReturnValue();";
                }
            }

            return ExecutionClosure::NOOP_INPUT;
        } catch (\Throwable $_e) {
            throw new BreakException($_e->getMessage());
        }
    }
}
