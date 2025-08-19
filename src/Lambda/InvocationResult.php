<?php

namespace DatPM\SlsTinker\Lambda;

use AsyncAws\Lambda\Result\InvocationResponse;

abstract class InvocationResult
{
    /** @var InvocationResponse */
    protected $result;

    /** @var mixed */
    protected $payload;

    /**
     * @param  mixed  $payload
     */
    public function __construct(InvocationResponse $result, $payload)
    {
        $this->result = $result;
        $this->payload = $payload;
    }

    abstract public function getOutput();

    public static function new(InvocationResponse $result, $payload)
    {
        if (config('sls-tinker.platform') === 'vapor') {
            return new VaporInvocationResult($result, $payload);
        } else {
            return new BrefInvocationResult($result, $payload);
        }
    }

    public function getLogs(): string
    {
        return base64_decode($this->result->getLogResult());
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
