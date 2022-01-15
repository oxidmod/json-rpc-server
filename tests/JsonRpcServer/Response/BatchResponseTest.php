<?php
declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Response;

use Oxidmod\JsonRpcServer\Response\BatchResponse;
use Oxidmod\JsonRpcServer\Response\Response;
use Oxidmod\JsonRpcServer\ResponseInterface;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class BatchResponseTest extends TestCase
{
    /**
     * @param array $responses
     * @param array $expected
     *
     * @dataProvider addResponseProvider
     */
    public function testAddResponse(array $responses, array $expected): void
    {
        $response = new BatchResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response);

        foreach ($responses as $item) {
            $response->addResponse($item);
        }

        $this->assertSame($expected, $response->toArray());
    }

    public function addResponseProvider(): iterable
    {
        yield 'empty batch response' => [
            [],
            [],
        ];

        yield 'not empty batch response' => [
            [
                Response::success(42, 'result'),
                Response::error(ServerError::internalError)
            ],
            [
                ['jsonrpc' => '2.0', 'id' => 42, 'result' => 'result'],
                [
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'error' => [
                        'code' => ServerError::internalError->value,
                        'message' => ServerError::internalError->message(),
                        'data' => ServerError::internalError->description()
                    ],
                ],
            ],
        ];
    }
}
