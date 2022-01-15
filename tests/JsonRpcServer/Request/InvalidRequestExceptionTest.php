<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Request;

use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class InvalidRequestExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new InvalidRequestException(null, ServerError::parseError);

        $this->assertInstanceOf(InvalidRequestException::class, $exception);
        $this->assertSame(null, $exception->id);
        $this->assertSame(ServerError::parseError, $exception->error);
        $this->assertSame([], $exception->getErrors());
        $this->assertSame(ServerError::parseError->value, $exception->getCode());
        $this->assertSame(ServerError::parseError->message(), $exception->getMessage());
    }

    public function testAddError(): void
    {
        $exception = new InvalidRequestException(42, ServerError::parseError);
        $this->assertFalse($exception->hasErrors());

        $exception->addError('field', 'error');
        $this->assertTrue($exception->hasErrors());
        $this->assertSame([['field' => 'field', 'error' => 'error']], $exception->getErrors());
    }
}
