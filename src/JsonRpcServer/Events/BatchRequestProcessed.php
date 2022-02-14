<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Events;

/**
 * @property-read RequestProcessed[] $childrenEvents
 */
class BatchRequestProcessed
{
    public function __construct(
        public readonly float $receivedAt,
        public readonly float $processedAt,
        public readonly array $childrenEvents
    ) {
    }
}
