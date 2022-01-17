<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\RequestHandler;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\ServerError;

abstract class AbstractParameters
{
    public readonly array $parameters;

    public function __construct(Request $request)
    {
        $errors = new InvalidRequestException($request->id, ServerError::invalidParamsError);
        $this->validateParameters($request, $errors);
        if ($errors->hasErrors()) {
            throw $errors;
        }

        $this->parameters = $request->params;
    }

    abstract protected function validateParameters(Request $request, InvalidRequestException $errors): void;
}
