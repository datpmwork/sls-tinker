<?php

namespace DatPM\SlsTinker\Shells;

use Psy\Shell;
use Psy\Configuration;

abstract class LambdaShell extends Shell
{
    protected $lambdaFunctionName;

    protected $contextRestored = false;

    public static function newLambdaShell(?Configuration $config = null, $lambdaFunctionName = '')
    {
        if (static::isRunningInLambda()) {
            return new RemoteLambdaShell($config, $lambdaFunctionName);
        }

        return new LocalLambdaShell($config, $lambdaFunctionName);
    }

    public function __construct(?Configuration $config = null, $lambdaFunctionName = '')
    {
        $this->lambdaFunctionName = $lambdaFunctionName;

        parent::__construct($config);
    }

    protected static function isRunningInLambda(): bool
    {
        return !empty(env('AWS_LAMBDA_RUNTIME_API'));
    }

    /**
     * @param $context
     * @return void
     */
    public function writeContextData($vars)
    {
        $context = base64_encode(serialize($vars));

        $this->writeStdout("[CONTEXT]{$context}[END_CONTEXT]");
    }

    /**
     * @param $output
     * @return array
     */
    public function extractContextData($output)
    {
        $pattern = '/(.*(?:\r?\n.*)*)\[CONTEXT\](.*?)\[END_CONTEXT\]/s';
        preg_match($pattern, $output, $matches);
        return empty($matches) ? null : [$matches[1], $matches[2]];
    }

    public function restoreContextData($context)
    {
        if ($returnVars = unserialize(base64_decode($context))) {
            $this->setScopeVariables($returnVars);
        }

        $this->contextRestored = true;
    }
}
