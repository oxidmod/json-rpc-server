<?php
declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer;

use Oxidmod\JsonRpcServer\ServerError;

class ServerErrorTest extends TestCase
{
    /**
     * @param int $code
     * @param ServerError $expected
     *
     * @dataProvider fromCodeProvider
     */
    public function testFromCode(int $code, ServerError $expected): void
    {
        $this->assertSame($expected, ServerError::fromCode($code));
    }

    public function fromCodeProvider(): iterable
    {
        yield 'parse error' => [
            -32700,
            ServerError::parseError,
        ];

        yield 'request error' => [
            -32600,
            ServerError::requestError,
        ];

        yield 'method not found error' => [
            -32601,
            ServerError::methodNotFoundError,
        ];

        yield 'invalid params error' => [
            -32602,
            ServerError::invalidParamsError,
        ];

        yield 'internal server error' => [
            -32603,
            ServerError::internalError,
        ];

        yield 'unknown error code' => [
            42,
            ServerError::internalError,
        ];
    }
}
