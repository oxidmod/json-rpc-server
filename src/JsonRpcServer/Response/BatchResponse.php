<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Response;

use Oxidmod\JsonRpcServer\ResponseInterface;

class BatchResponse implements ResponseInterface
{
    /** @var ResponseInterface[] */
    private array $responses = [];

    public function addResponse(ResponseInterface $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    public function toArray(): array
    {
        return array_map(function (ResponseInterface $response) {
            return $response->toArray();
        }, $this->responses);
    }
}
