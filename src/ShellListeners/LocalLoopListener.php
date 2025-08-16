<?php

namespace DatPM\SlsTinker\ShellListeners;

use AsyncAws\Core\Exception\Http\ClientException;
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
            15 * 60 // maximum duration on Lambda
        );

        return $lambda->invoke($this->lambdaFunctionName, json_encode(implode(' ', $arguments)));
    }

    /**
     * @param  LambdaShell  $shell
     */
    public function onExecute(Shell $shell, string $code)
    {
        if ($code == '\Psy\Exception\BreakException::exitShell();') {
            return $code;
        }

        $vars = $shell->getScopeVariables(false);
        try {
            // Evaluate the current code buffer
            $result = $this->invokeLambdaFunction([
                'sls-tinker',
                $this->lambdaFunctionName,
                '--execute',
                $code,
                '--context',
                base64_encode(serialize($vars)),
            ]);

            $rawOutput = $result->getPayload()['output'];

            if ([$output, $context] = $shell->extractContextData($rawOutput)) {
                $shell->writeStdout($output);

                return "extract(unserialize(base64_decode('$context')));";
            }

            return ExecutionClosure::NOOP_INPUT;
        } catch (ClientException $_e) {
            throw new BreakException($_e->getMessage());
        } catch (\Throwable $throwable) {
            throw new ThrowUpException($throwable);
        }
    }
}
