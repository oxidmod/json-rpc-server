<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\RequestHandler;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\JsonRpcServer\RequestHandlerInterface;
use Oxidmod\JsonRpcServer\Response\Response;
use Oxidmod\JsonRpcServer\ResponseInterface;

abstract class AbstractHandler implements RequestHandlerInterface
{
    public function handle(Request $request): ResponseInterface
    {
        try {
            $params = $this->getParameters($request);
        } catch (InvalidRequestException $exception) {
            return Response::fromRequestException($exception);
        }

        return $this->doHandleRequest($request->id, $params);
    }

    /**
     * @param Request $request
     * @return AbstractParameters
     *
     * @throws InvalidRequestException
     */
    abstract protected function getParameters(Request $request): AbstractParameters;

    /**
     * @param int|string $requestId
     * @param AbstractParameters $parameters
     * @return ResponseInterface
     **/
    abstract protected function doHandleRequest(
        int|string $requestId,
        AbstractParameters $parameters
    ): ResponseInterface;
}
