<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Request;

use Closure;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class RequestTest extends TestCase
{
    public function testConstruct(): void
    {
        $request = new Request(self::REQUEST_DATA);

        $this->assertSame(self::REQUEST_DATA['id'], $request->id);
        $this->assertSame(self::REQUEST_DATA['method'], $request->method);
        $this->assertSame(self::REQUEST_DATA['params'], $request->params);
    }

    /**
     * @param array $params
     * @param Closure $asserts
     *
     * @dataProvider constructExceptionProvider
     */
    public function testConstructException(array $params, Closure $asserts): void
    {
        $this->expectException(InvalidRequestException::class);

        try {
            new Request($params);
        } catch (InvalidRequestException $exception) {
            $asserts->call($this, $exception);

            throw $exception;
        }
    }

    public function constructExceptionProvider(): iterable
    {
        yield 'empty request' => [
            [],
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertNull($exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame([
                    ['field' => 'jsonrpc', 'error' => 'Field "jsonrpc" is required and must be equal to "2.0".'],
                    ['field' => 'id', 'error' => 'Field "id" is required and must be a string or an integer.'],
                    ['field' => 'method', 'error' => 'Field "method" is required and must be a string.'],
                ], $exception->getErrors());
            }),
        ];

        yield 'invalid jsonrpc' => [
            array_merge([], self::REQUEST_DATA, ['jsonrpc' => '3.0']),
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertSame(42, $exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame([
                    ['field' => 'jsonrpc', 'error' => 'Field "jsonrpc" is required and must be equal to "2.0".'],
                ], $exception->getErrors());
            }),
        ];

        yield 'invalid id' => [
            array_merge([], self::REQUEST_DATA, ['id' => 42.2]),
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertNull($exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame([
                    ['field' => 'id', 'error' => 'Field "id" is required and must be a string or an integer.'],
                ], $exception->getErrors());
            }),
        ];

        yield 'invalid method' => [
            array_merge([], self::REQUEST_DATA, ['method' => 13]),
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertSame(42, $exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame([
                    ['field' => 'method', 'error' => 'Field "method" is required and must be a string.'],
                ], $exception->getErrors());
            }),
        ];

        yield 'invalid params' => [
            array_merge([], self::REQUEST_DATA, ['params' => 'test']),
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertSame(42, $exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame([
                    ['field' => 'params', 'error' => 'Field "params" must be a valid JSON object.'],
                ], $exception->getErrors());
            }),
        ];
    }
}
