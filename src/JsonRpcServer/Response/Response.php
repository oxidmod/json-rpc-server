<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Response;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\ResponseInterface;
use Oxidmod\JsonRpcServer\ServerError;

class Response implements ResponseInterface
{
    private function __construct(
        private string|int|null $id,
        private mixed $result,
        private ?array $error
    ) {}

    public static function success(string|int $id, mixed $result): self
    {
        return new self($id, $result, null);
    }

    public static function error(ServerError $error, string|int|null $id = null, mixed $data = null): self
    {
        return new self($id, null, [
            'code' => $error->value,
            'message' => $error->message(),
            'data' => $data ?? $error->description(),
        ]);
    }

    public static function fromRequestException(InvalidRequestException $exception): self
    {
        return self::error($exception->error, $exception->id, $exception->getErrors());
    }

    public function toArray(): array
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => $this->id,
        ];

        if (null !== $this->error) {
            $data['error'] = $this->error;
        } else {
            $data['result'] = $this->result;
        }

        return $data;
    }
}
