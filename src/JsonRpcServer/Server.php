<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer;

use Oxidmod\JsonRpcServer\Events\BatchRequestProcessed;
use Oxidmod\JsonRpcServer\Events\RequestFailed;
use Oxidmod\JsonRpcServer\Events\RequestProcessed;
use Oxidmod\JsonRpcServer\Request\BatchRequest;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Parser;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\Response\BatchResponse;
use Oxidmod\JsonRpcServer\Response\Response;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Server
{
    /** @var RequestHandlerInterface[] */
    private array $handlers = [];

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        private Parser $parser,
        iterable $handlers
    ) {
        /** @var RequestHandlerInterface $handler */
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getSupportedMethod()] = $handler;
        }
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(string $content): ResponseInterface
    {
        $receivedAt = microtime(true);

        try {
            $item = $this->parser->parseRawContent($content);
        } catch (Throwable $exception) {
            $item = $exception;
        }

        [$response, $event] = $this->process($item, $receivedAt);

        if (!empty($this->dispatcher)) {
            $this->dispatcher->dispatch($event);
        }

        return $response;
    }

    private function process(mixed $item, float $receivedAt): array
    {
        return match (true) {
            $item instanceof Request => $this->handleRequest($item, $receivedAt),
            $item instanceof BatchRequest => $this->handleBatchRequest($item, $receivedAt),
            default => $this->handleError($item, $receivedAt),
        };
    }

    private function handleRequest(Request $request, float $receivedAt): array
    {
        $handler = $this->handlers[$request->method] ?? null;

        try {
            $response = match (true) {
                $handler instanceof RequestHandlerInterface => $handler->handle($request),
                default => Response::error(ServerError::methodNotFoundError, $request->id),
            };
        } catch (Throwable $e) {
            $response = Response::error(ServerError::internalError, $request->id, [['error' => $e->getMessage()]]);
        }

        $event = new RequestProcessed($receivedAt, microtime(true), $request, $response);

        return [$response, $event];
    }

    private function handleBatchRequest(BatchRequest $batch, float $receivedAt): array
    {
        $batchResponse = new BatchResponse();
        $events = [];
        foreach ($batch->requests() as $request) {
            [$response, $event] = $this->process($request, microtime(true));

            $batchResponse->addResponse($response);
            $events[] = $event;
        }

        $event = new BatchRequestProcessed($receivedAt, microtime(true), $events);

        return [$batchResponse, $event];
    }

    private function handleError(mixed $error, float $receivedAt): array
    {
        $response = match (true) {
            $error instanceof InvalidRequestException => Response::fromRequestException($error),
            default => Response::error(ServerError::requestError),
        };

        $event = new RequestFailed($receivedAt, microtime(true), $response);

        return [$response, $event];
    }
}
