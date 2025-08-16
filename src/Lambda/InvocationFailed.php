<?php

namespace DatPM\SlsTinker\Lambda;

class InvocationFailed extends \Exception
{
    /** @var InvocationResult */
    private $invocationResult;

    public function __construct(InvocationResult $invocationResult)
    {
        $this->invocationResult = $invocationResult;
        $message = $invocationResult->getPayload()['errorMessage'] ?? 'Unknown error';

        parent::__construct($message);
    }

    /**
     * @return InvocationResult
     */
    public function getInvocationResult(): InvocationResult
    {
        return $this->invocationResult;
    }

    public function getInvocationLogs(): string
    {
        return $this->invocationResult->getLogs();
    }
}
