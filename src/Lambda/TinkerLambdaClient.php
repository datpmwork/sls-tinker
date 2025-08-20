<?php

namespace DatPM\SlsTinker\Lambda;

use AsyncAws\Lambda\LambdaClient;
use Symfony\Component\HttpClient\HttpClient;

class TinkerLambdaClient
{
    /** @var LambdaClient */
    private $lambda;

    public function __construct(string $region, string $profile)
    {
        $lambdaConfig = [
            'region' => $region,
            'profile' => $profile,
        ];

        $lambdaEndpoint = config('sls-tinker.lambda_endpoint');
        if (! empty($lambdaEndpoint)) {
            $lambdaConfig['endpoint'] = $lambdaEndpoint;
        }

        $this->lambda = new LambdaClient(
            $lambdaConfig,
            null,
            HttpClient::create([
                'timeout' => config('sls-tinker.lambda_timeout'),
            ])
        );
    }

    /**
     * Synchronously invoke a function.
     *
     * @param  mixed  $event  Event data (can be null).
     *
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
        $invocationResult = InvocationResult::new($rawResult, $resultPayload);

        $error = $rawResult->getFunctionError();
        if ($error) {
            throw new InvocationFailed($invocationResult);
        }

        return $invocationResult;
    }
}
