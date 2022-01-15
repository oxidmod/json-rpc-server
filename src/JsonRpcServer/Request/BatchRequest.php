<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Request;

use Generator;

class BatchRequest
{
    public function __construct(
        private array $data
    ) {}

    public function requests(): Generator
    {
        foreach ($this->data as $requestData) {
            try {
                yield new Request($requestData);
            } catch (InvalidRequestException $exception) {
                yield $exception;
            }
        }
    }
}
