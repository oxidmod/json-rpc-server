<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer;

use Oxidmod\JsonRpcServer\Request\BatchRequest;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Parser;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\Response\BatchResponse;
use Oxidmod\JsonRpcServer\Response\Response;
use Throwable;

class Server
{
    /** @var RequestHandlerInterface[] */
    private array $handlers = [];

    public function __construct(
        private Parser $parser,
        iterable $handlers
    ) {
        /** @var RequestHandlerInterface $handler */
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getSupportedMethod()] = $handler;
        }
    }

    public function handle(string $content): ResponseInterface
    {
        try {
            $item = $this->parser->parseRawContent($content);
        } catch (Throwable $exception) {
            $item = $exception;
        } finally {
            return $this->process($item);
        }
    }

    private function process(mixed $item): ResponseInterface
    {
        return match (true) {
            $item instanceof Request => $this->handleRequest($item),
            $item instanceof BatchRequest => $this->handleBatchRequest($item),
            $item instanceof InvalidRequestException => Response::fromRequestException($item),
            default => Response::error(ServerError::requestError),
        };
    }

    private function handleRequest(Request $request): ResponseInterface
    {
        $handler = $this->handlers[$request->method] ?? null;

        try {
            return match (true) {
                $handler instanceof RequestHandlerInterface => $handler->handle($request),
                default => Response::error(ServerError::methodNotFoundError, $request->id),
            };
        } catch (Throwable $e) {
            return Response::error(ServerError::internalError, $request->id, [['error' => $e->getMessage()]]);
        }
    }

    private function handleBatchRequest(BatchRequest $batch): ResponseInterface
    {
        $response = new BatchResponse();
        foreach ($batch->requests() as $request) {
            $response->addResponse($this->process($request));
        }

        return $response;
    }
}
