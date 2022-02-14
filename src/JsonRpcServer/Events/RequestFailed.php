<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Events;

use Oxidmod\JsonRpcServer\ResponseInterface;

class RequestFailed
{
    public function __construct(
        public readonly float $receivedAt,
        public readonly float $processedAt,
        public readonly ResponseInterface $response
    ) {
    }
}
