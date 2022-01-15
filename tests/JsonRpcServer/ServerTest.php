<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer;

use Oxidmod\JsonRpcServer\Request\BatchRequest;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Parser;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\RequestHandlerInterface;
use Oxidmod\JsonRpcServer\Response\BatchResponse;
use Oxidmod\JsonRpcServer\Response\Response;
use Oxidmod\JsonRpcServer\ResponseInterface;
use Oxidmod\JsonRpcServer\Server;
use Oxidmod\JsonRpcServer\ServerError;

class ServerTest extends TestCase
{
    /**
     * @param Server $server
     * @param string $content
     * @param ResponseInterface $response
     *
     * @dataProvider handleProvider
     */
    public function testHandle(
        Server $server,
        string $content,
        ResponseInterface $response
    ): void {
        $this->assertEquals($response, $server->handle($content));
    }

    public function handleProvider(): iterable
    {
        $request = $this->createRequest();
        $content = 'some content';

        yield 'no request handlers' => [
            new Server(
                $this->createParserMock($content, $request),
                []
            ),
            $content,
            Response::error(ServerError::methodNotFoundError, $request->id)
        ];

        yield 'method not found' => [
            new Server(
                $this->createParserMock($content, $request),
                [$this->createRequestHandlerMock('test_method')]
            ),
            $content,
            Response::error(ServerError::methodNotFoundError, $request->id)
        ];

        $response = $this->createMock(ResponseInterface::class);
        yield 'single request' => [
            new Server(
                $this->createParserMock($content, $request),
                [$this->createRequestHandlerMock($request->method, [compact('request', 'response')])]
            ),
            $content,
            $response
        ];


        $request2Method = 'request_2_method';
        $request2Id = 13;
        $request2 = $this->createRequest([
            'id' => $request2Id,
            'method' => $request2Method,
        ]);
        $response2 = $this->createMock(ResponseInterface::class);

        $batchRequest = $this->createMock(BatchRequest::class);
        $batchRequest->expects($this->once())
            ->method('requests')
            ->willReturn((function () use ($request, $request2) {
                yield $request;
                yield $request2;
            })());

        yield 'batch request' => [
            new Server(
                $this->createParserMock($content, $batchRequest),
                [
                    $this->createRequestHandlerMock('not_to_be_called'),
                    $this->createRequestHandlerMock(
                        $request->method,
                        [
                            ['request' => $request, 'response' => $response],
                        ]
                    ),
                    $this->createRequestHandlerMock(
                        $request2->method,
                        [
                            ['request' => $request2, 'response' => $response2],
                        ]
                    ),
                ]
            ),
            $content,
            (new BatchResponse())
                ->addResponse($response)
                ->addResponse($response2)
        ];

        $requestException = new InvalidRequestException(13, ServerError::parseError);
        $errorResponse = Response::fromRequestException($requestException);
        yield 'invalid request' => [
            new Server(
                $this->createParserMock($content, $requestException),
                []
            ),
            $content,
            $errorResponse,
        ];

        $batchRequest = $this->createMock(BatchRequest::class);
        $batchRequest->expects($this->once())
            ->method('requests')
            ->willReturn((function () use ($request, $requestException) {
                yield $request;
                yield $requestException;
            })());
        yield 'invalid request in batch' => [
            new Server(
                $this->createParserMock($content, $batchRequest),
                [
                    $this->createRequestHandlerMock($request->method, [compact('request', 'response')]),
                ],
            ),
            $content,
            (new BatchResponse())
                ->addResponse($response)
                ->addResponse($errorResponse)
        ];

        $batchRequest = $this->createMock(BatchRequest::class);
        $batchRequest->expects($this->once())
            ->method('requests')
            ->willReturn((function () use ($request, $requestException) {
                yield 'unexpected batch item';
            })());
        yield 'parser unexpected result' => [
            new Server(
                $this->createParserMock($content, $batchRequest),
                []
            ),
            $content,
            (new BatchResponse())
                ->addResponse(Response::error(ServerError::requestError)),
        ];
    }

    private function createParserMock(string $content, Request|BatchRequest|InvalidRequestException $result): Parser
    {
        $mock = $this->createMock(Parser::class);
        $invocation = $mock->expects($this->once())->method('parseRawContent')->with($content);

        if ($result instanceof InvalidRequestException) {
            $invocation->willThrowException($result);
        } else {
            $invocation->willReturn($result);
        }

        return $mock;
    }

    private function createRequestHandlerMock(
        string $method,
        array $calls = []
    ): RequestHandlerInterface {
        $mock = $this->createMock(RequestHandlerInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getSupportedMethod')
            ->willReturn($method);

        if (!empty($calls)) {
            $requests = array_map(
                fn(Request $request) => [$request],
                array_column($calls, 'request')
            );

            $mock->expects($this->exactly(count($calls)))
                ->method('handle')
                ->withConsecutive(...$requests)
                ->willReturnOnConsecutiveCalls(...array_column($calls, 'response'));
        } else {
            $mock->expects($this->never())->method('handle');
        }

        return $mock;
    }
}
