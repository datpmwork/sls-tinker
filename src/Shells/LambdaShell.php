<?php

namespace DatPM\SlsTinker\Shells;

use Psy\Shell;
use Psy\Configuration;
use Psy\CodeCleaner\NoReturnValue;
use Illuminate\Console\OutputStyle;

abstract class LambdaShell extends Shell
{
    protected string $platform;

    protected string $lambdaFunctionName;

    protected bool $contextRestored = false;

    protected OutputStyle $rawOutput;

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
        return ! empty($_SERVER['AWS_LAMBDA_RUNTIME_API']);
    }

    /**
     * The output interface implementation.
     */
    public function setRawOutput($rawOutput)
    {
        $this->rawOutput = $rawOutput;
    }

    public function getRawOutput(): OutputStyle
    {
        return $this->rawOutput;
    }

    protected function writeStartupMessage() {}

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
        if ($ret instanceof NoReturnValue) {
            return;
        }

        $prompt = '= ';
        $indent = \str_repeat(' ', \strlen($prompt));
        $formatted = $this->presentValue($ret);
        $formattedRetValue = \sprintf('<whisper>%s</whisper>', $prompt);
        $formatted = $formattedRetValue.str_replace(\PHP_EOL, \PHP_EOL.$indent, $formatted);
        $this->writeStdout("[RETURN]{$formatted}[END_RETURN]");
    }

    /**
     * @return list<string>|null
     */
    public function extractContextData(string $output): ?array
    {
        $output = trim($output);
        // First, extract RETURN section if it exists
        if (preg_match('/\[RETURN\](.*?)\[END_RETURN\]/s', $output, $returnMatches)) {
            $returnValue = $returnMatches[1];
            // Remove RETURN section to work with the rest
            $output = (string) preg_replace('/\[RETURN\].*?\[END_RETURN\]/s', '', $output);
        } else {
            $returnValue = '';
        }

        // Then extract CONTEXT section if it exists
        if (preg_match('/\[CONTEXT\](.*?)\[END_CONTEXT\]/s', $output, $contextMatches)) {
            $context = $contextMatches[1];
            // Remove CONTEXT section to get the before part
            $output = (string) preg_replace('/\[CONTEXT\].*?\[END_CONTEXT\]\n?/s', '', $output);
        } else {
            $context = '';
        }

        // Only return null if we couldn't find any meaningful structure
        if (empty($output) && empty($context) && empty($returnValue)) {
            return null;
        }

        return [$output, $context, $returnValue];
    }

    public function restoreContextData($context)
    {
        if ($returnVars = unserialize(base64_decode($context))) {
            $this->setScopeVariables($returnVars);
        }

        $this->contextRestored = true;
    }
}
