<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer;

use Oxidmod\JsonRpcServer\Request\Request;

interface RequestHandlerInterface
{
    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function handle(Request $request): ResponseInterface;

    public function getSupportedMethod(): string;
}
