<?php

namespace DatPM\SlsTinker\Lambda;

use AsyncAws\Lambda\LambdaClient;
use Symfony\Component\HttpClient\HttpClient;

class TinkerLambdaClient
{
    /** @var LambdaClient */
    private $lambda;

    public function __construct(string $region, string $profile, int $timeout = 60)
    {
        $this->lambda = new LambdaClient(
            [
                'region' => $region,
                'profile' => $profile,
                'endpoint' => config('sls-tinker.lambda_endpoint'),
            ],
            null,
            HttpClient::create([
                'timeout' => $timeout,
            ])
        );
    }

    /**
     * Synchronously invoke a function.
     *
     * @param mixed $event Event data (can be null).
     * @throws InvocationFailed
     */
    public function invoke(string $functionName, $event = null): InvocationResult
    {
        $rawResult = $this->lambda->invoke([
            'FunctionName' => $functionName,
            'LogType' => 'Tail',
            'Payload' => $event ?? '',
        ]);

        $resultPayload = json_decode($rawResult->getPayload(), true);
        $invocationResult = new InvocationResult($rawResult, $resultPayload);

        $error = $rawResult->getFunctionError();
        if ($error) {
            throw new InvocationFailed($invocationResult);
        }

        return $invocationResult;
    }
}
