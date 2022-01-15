<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer;

// phpcs:ignoreFile
enum ServerError: int
{
    case parseError = -32700;
    case requestError = -32600;
    case methodNotFoundError = -32601;
    case invalidParamsError = -32602;
    case internalError = -32603;

    public static function fromCode(int $code): self
    {
        return match ($code) {
            self::parseError->value => self::parseError,
            self::requestError->value => self::requestError,
            self::methodNotFoundError->value => self::methodNotFoundError,
            self::invalidParamsError->value => self::invalidParamsError,
            default => self::internalError,
        };
    }

    public function message(): string
    {
        return match ($this) {
            self::parseError => 'Parse error',
            self::requestError => 'Invalid Request',
            self::methodNotFoundError => 'Method not found',
            self::invalidParamsError => 'Invalid params',
            self::internalError => 'Internal error',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::parseError => 'Invalid JSON was received by the server. An error occurred on the server while parsing the JSON text.',
            self::requestError => 'The JSON sent is not a valid Request object.',
            self::methodNotFoundError => 'The method does not exist / is not available.',
            self::invalidParamsError => 'Invalid method parameter(s).',
            self::internalError => 'Internal JSON-RPC error.',
        };
    }
}
