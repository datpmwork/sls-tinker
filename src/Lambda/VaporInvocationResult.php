<?php

namespace DatPM\SlsTinker\Lambda;

class VaporInvocationResult extends InvocationResult
{
    public function getOutput()
    {
        $output = base64_decode($this->payload['output'] ?? '');
        if (! $output) {
            throw new \RuntimeException('Failed to decode output from base64.');
        }

        $outputJson = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode output JSON: '.json_last_error_msg());
        }

        return data_get($outputJson, 'output', '');
    }
}
