<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\RequestHandler;

use Closure;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\RequestHandler\AbstractParameters;
use Oxidmod\JsonRpcServer\Response\Response;
use Oxidmod\JsonRpcServer\ResponseInterface;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class AbstractHandlerTest extends TestCase
{
    public function testConstruct(): void
    {
        $request = $this->createRequest();
        $response = $this->createMock(ResponseInterface::class);
        $parameters = $this->createRequestParameters($request);

        $instance = $this->createRequestHandler($request, $response, $parameters);

        $this->assertSame($response, $instance->handle($request));
    }

    public function testConstructException(): void
    {
        $request = $this->createRequest();
        $errors = new InvalidRequestException($request->id, ServerError::requestError);
        $errors->addError('test', 'error');
        $response = Response::fromRequestException($errors);


        $instance = $this->createRequestHandler(
            request: $request,
            response: $response,
            getParameters: Closure::fromCallable(function (Request $request) use ($errors) {
                throw $errors;
            }),
            doHandleRequest: Closure::fromCallable(function (int|string $requestId, AbstractParameters $parameters) {
                $this->fail(sprintf('"%s" must not be called', __METHOD__));
            })
        );

        $this->assertEquals($response, $instance->handle($request));
    }
}
