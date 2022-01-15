<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Response;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Response\Response;
use Oxidmod\JsonRpcServer\ResponseInterface;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class ResponseTest extends TestCase
{
    public function testSuccess(): void
    {
        $response = Response::success(42, 'result data');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame([
            'jsonrpc' => '2.0',
            'id' => 42,
            'result' => 'result data',
        ], $response->toArray());
    }

    /**
     * @param ServerError $error
     * @param int|string|null $id
     * @param mixed $data
     * @param array $expected
     *
     * @dataProvider errorProvider
     */
    public function testError(
        ServerError $error,
        int|string|null $id,
        mixed $data,
        array $expected
    ): void {
        $response = Response::error($error, $id, $data);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($expected, $response->toArray());
    }

    public function testFromRequestException(): void
    {
        $exception = (new InvalidRequestException(42, ServerError::invalidParamsError))
            ->addError('params.test', 'test error')
            ->addError('params.another_test', 'another test error')
        ;

        $expected = $this->prepareErrorResponse(
            42,
            ServerError::invalidParamsError,
            [
                ['field' => 'params.test', 'error' => 'test error'],
                ['field' => 'params.another_test', 'error' => 'another test error'],
            ]
        );

        $response = Response::fromRequestException($exception);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($expected, $response->toArray());
    }

    public function errorProvider(): iterable
    {
        yield 'parse error' => [
            ServerError::parseError,
            null,
            null,
            $this->prepareErrorResponse(null, ServerError::parseError, ServerError::parseError->description()),
        ];

        yield 'request error' => [
            ServerError::requestError,
            'string_id',
            ['custom' => 'data'],
            $this->prepareErrorResponse('string_id', ServerError::requestError, ['custom' => 'data']),
        ];

        yield 'method not found error' => [
            ServerError::methodNotFoundError,
            42,
            13, // custom primitive data
            $this->prepareErrorResponse(42, ServerError::methodNotFoundError, 13),
        ];

        yield 'invalid params error' => [
            ServerError::invalidParamsError,
            42,
            null,
            $this->prepareErrorResponse(
                42,
                ServerError::invalidParamsError,
                ServerError::invalidParamsError->description()
            ),
        ];

        yield 'internal server error' => [
            ServerError::internalError,
            null,
            'custom data string',
            $this->prepareErrorResponse(null, ServerError::internalError, 'custom data string'),
        ];
    }

    private function prepareErrorResponse(int|string|null $id, ServerError $error, mixed $data): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $error->value,
                'message' => $error->message(),
                'data' => $data,
            ],
        ];
    }
}
