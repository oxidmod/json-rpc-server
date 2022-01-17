<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\RequestHandler;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class AbstractParametersTest extends TestCase
{
    public function testConstruct(): void
    {
        $request = $this->createRequest();

        $instance = $this->createRequestParameters($request);

        $this->assertSame($request->params, $instance->parameters);
    }

    public function testConstructException(): void
    {
        $this->expectException(InvalidRequestException::class);

        $request = $this->createRequest();

        $this->createRequestParameters(
            $request,
            \Closure::fromCallable(function (Request $request, InvalidRequestException $errors) {
                $errors->addError('test', 'error');
            })
        );
    }
}
