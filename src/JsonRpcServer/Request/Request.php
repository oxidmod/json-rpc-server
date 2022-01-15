<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Request;

use Oxidmod\JsonRpcServer\ServerError;

# TODO: add notification support
class Request
{
    public readonly int | string $id;

    public readonly string $method;

    public readonly array $params;

    public function __construct(array $request)
    {
        [$this->id, $this->method, $this->params] = $this->extractRequestFields($request);
    }

    private function extractRequestFields(array $request): array
    {
        $id = $this->extractRequestId($request);
        $exception = new InvalidRequestException($id, ServerError::requestError);

        $version = $request['jsonrpc'] ?? null;
        if ($version !== '2.0') {
            $exception->addError('jsonrpc', 'Field "jsonrpc" is required and must be equal to "2.0".');
        }

        if (empty($id) || (!is_string($id) && !is_int($id))) {
            $exception->addError('id', 'Field "id" is required and must be a string or an integer.');
        }

        $method = $request['method'] ?? null;
        if (empty($method) || !is_string($method)) {
            $exception->addError('method', 'Field "method" is required and must be a string.');
        }

        $params = $request['params'] ?? [];
        if (!is_array($params)) {
            $exception->addError('params', 'Field "params" must be a valid JSON object.');
        }

        if ($exception->hasErrors()) {
            throw $exception;
        }

        return [$id, $method, $params];
    }

    private function extractRequestId(array $request): int|string|null
    {
        $id = $request['id'] ?? null;
        return match (true) {
            is_null($id) || is_int($id) || is_string($id) => $id,
            default => null,
        };
    }
}
