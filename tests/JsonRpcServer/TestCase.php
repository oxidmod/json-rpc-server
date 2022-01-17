<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer;

use Closure;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\RequestHandler\AbstractHandler;
use Oxidmod\JsonRpcServer\RequestHandler\AbstractParameters;
use Oxidmod\JsonRpcServer\ResponseInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const REQUEST_DATA = [
        'jsonrpc' => '2.0',
        'id' => 42,
        'method' => 'request_method',
        'params' => [
            'param_1' => 13,
        ],
    ];

    protected function createRequest(array $overrides = []): Request
    {
        return new Request(array_merge([], self::REQUEST_DATA, $overrides));
    }

    protected function createRequestParameters(Request $request, Closure $validateParameters = null): AbstractParameters
    {
        $validateParameters = $validateParameters ?? Closure::fromCallable(
            function (Request $request, InvalidRequestException $errors) {
                // all requests are valid by default
            }
        );

        return new class ($request, $validateParameters) extends AbstractParameters {
            public function __construct(
                Request $request,
                private Closure $validateParameters
            ) {
                parent::__construct($request);
            }

            protected function validateParameters(Request $request, InvalidRequestException $errors): void
            {
                $this->validateParameters->call($this, $request, $errors);
            }
        };
    }

    protected function createRequestHandler(
        Request $request,
        ResponseInterface $response,
        AbstractParameters $parameters = null,
        Closure $getParameters = null,
        Closure $doHandleRequest = null
    ): AbstractHandler {
        if (null === $getParameters) {
            $parameters = $parameters ?? $this->createRequestParameters($request);
            $getParameters = \Closure::fromCallable(
                function (Request $givenRequest) use ($request, $parameters) {
                    $this->assertSame($request, $givenRequest);

                    return $parameters;
                }
            );
        }

        if (null === $doHandleRequest) {
            $doHandleRequest = \Closure::fromCallable(
                function (
                    int|string $requestId,
                    AbstractParameters $givenParameters
                ) use (
                    $request,
                    $response,
                    $parameters
                ) {
                    $this->assertSame($request->id, $requestId);
                    $this->assertEquals($parameters, $givenParameters);

                    return $response;
                }
            );
        }

        return new class ($request, $this, $getParameters, $doHandleRequest) extends AbstractHandler {
            public function __construct(
                private Request $request,
                private TestCase $testCase,
                private \Closure $getParameters,
                private \Closure $doHandleRequest
            ) {
            }

            protected function getParameters(Request $request): AbstractParameters
            {
                return $this->getParameters->call($this->testCase, $request);
            }

            protected function doHandleRequest(int|string $requestId, AbstractParameters $parameters): ResponseInterface
            {
                return $this->doHandleRequest->call($this->testCase, $requestId, $parameters);
            }

            public function getSupportedMethod(): string
            {
                return $this->request->method;
            }
        };
    }
}
