<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer;

interface ResponseInterface
{
    public function toArray(): array;
}
