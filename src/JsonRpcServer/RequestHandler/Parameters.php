<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\RequestHandler;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;

/**
 * Simple parameters bag without validation
 */
class Parameters extends AbstractParameters
{
    protected function validateParameters(Request $request, InvalidRequestException $errors): void
    {
    }
}
