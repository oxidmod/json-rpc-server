<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\RequestHandler;

use Oxidmod\JsonRpcServer\RequestHandler\Parameters;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class ParametersTest extends TestCase
{
    public function testConstruct(): void
    {
        $request = $this->createRequest();

        $parameters = new Parameters($request);

        $this->assertSame($request->params, $parameters->parameters);
    }
}
