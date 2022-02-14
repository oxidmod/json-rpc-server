<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Events;

use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\ResponseInterface;

class RequestProcessed
{
    public function __construct(
        public readonly float $receivedAt,
        public readonly float $processedAt,
        public readonly Request $request,
        public readonly ResponseInterface $response
    ) {
    }
}
