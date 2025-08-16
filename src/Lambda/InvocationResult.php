<?php

namespace DatPM\SlsTinker\Lambda;

use AsyncAws\Lambda\Result\InvocationResponse;

class InvocationResult
{
    /** @var InvocationResponse */
    private $result;

    /** @var mixed */
    private $payload;

    /**
     * @param  mixed  $payload
     */
    public function __construct(InvocationResponse $result, $payload)
    {
        $this->result = $result;
        $this->payload = $payload;
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
