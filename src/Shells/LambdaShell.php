<?php

namespace DatPM\SlsTinker\Shells;

use Psy\Configuration;
use Psy\Shell;

abstract class LambdaShell extends Shell
{
    protected $lambdaFunctionName;

    protected $contextRestored = false;

    protected $platform;

    public static function newLambdaShell(?Configuration $config = null, $lambdaFunctionName = '', $platform = '')
    {
        if (static::isRunningInLambda()) {
            return new RemoteLambdaShell($config, $lambdaFunctionName, $platform);
        }

        return new LocalLambdaShell($config, $lambdaFunctionName, $platform);
    }

    public function __construct(?Configuration $config = null, $lambdaFunctionName = '', $platform = '')
    {
        $this->lambdaFunctionName = $lambdaFunctionName;
        $this->platform = $platform;

        parent::__construct($config);
    }

    protected static function isRunningInLambda(): bool
    {
        return ! empty(env('AWS_LAMBDA_RUNTIME_API'));
    }

    /**
     * @param  $context
     * @return void
     */
    public function writeContextData($vars)
    {
        $context = base64_encode(serialize($vars));

        $this->writeStdout("[CONTEXT]{$context}[END_CONTEXT]");
    }

    /**
     * @return array
     */
    public function extractContextData($output)
    {
        if ($this->platform == 'vapor') {
            $output = base64_decode($output);
        }
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
