<?php

namespace DatPM\SlsTinker\Shells;

use Psy\Configuration;
use Psy\Shell;
use Illuminate\Console\OutputStyle;

abstract class LambdaShell extends Shell
{
    protected $lambdaFunctionName;

    protected $contextRestored = false;

    protected $platform;

    protected $rawOutput;

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
     * The output interface implementation.
     *
     */
    public function setRawOutput($rawOutput)
    {
        $this->rawOutput = $rawOutput;
    }

    public function getRawOutput(): OutputStyle
    {
        return $this->rawOutput;
    }

    protected function writeStartupMessage()
    {

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
     * @param  $context
     * @return void
     */
    public function writeReturnValueData($ret)
    {
        $context = base64_encode(serialize($ret));

        $this->writeStdout("[RETURN]{$context}[END_RETURN]");
    }

    /**
     * @return array
     */
    public function extractContextData($output)
    {
        $pattern = '/(.*(?:\r?\n.*)*)\[CONTEXT\](.*?)\[END_CONTEXT\]\n\[RETURN\](.*?)\[END_RETURN\]/s';
        preg_match($pattern, $output, $matches);

        return empty($matches) ? null : [$matches[1], $matches[2], $matches[3]];
    }

    public function restoreContextData($context)
    {
        if ($returnVars = unserialize(base64_decode($context))) {
            $this->setScopeVariables($returnVars);
        }

        $this->contextRestored = true;
    }
}
