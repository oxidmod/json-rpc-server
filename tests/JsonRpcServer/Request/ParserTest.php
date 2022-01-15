<?php
declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Request;

use Closure;
use Oxidmod\JsonRpcServer\Request\BatchRequest;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Parser;
use Oxidmod\JsonRpcServer\ServerError;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class ParserTest extends TestCase
{
    /**
     * @param string $content
     * @param mixed $expected
     *
     * @dataProvider parseRawContentProvider
     */
    public function testParseRawContent(string $content, mixed $expected): void
    {
        $parse = new Parser();

        $this->assertEquals($expected, $parse->parseRawContent($content));
    }

    public function parseRawContentProvider(): iterable
    {
        yield 'single request' => [
            json_encode(self::REQUEST_DATA),
            $this->createRequest(),
        ];

        yield 'batch request' => [
            json_encode([self::REQUEST_DATA]),
            new BatchRequest([self::REQUEST_DATA]),
        ];
    }

    /**
     * @param string $content
     * @param Closure $asserts
     *
     * @dataProvider parseRawContentExceptionProvider
     */
    public function testParseRawContentException(string $content, Closure $asserts): void
    {
        $this->expectException(InvalidRequestException::class);

        $parser = new Parser();
        try {
            $parser->parseRawContent($content);
        } catch (InvalidRequestException $exception) {
            $asserts->call($this, $exception);

            throw $exception;
        }
    }

    public function parseRawContentExceptionProvider(): iterable
    {
        $invalidContentAsserts = Closure::fromCallable(function (InvalidRequestException $exception) {
            $this->assertNull($exception->id);
            $this->assertSame(ServerError::parseError, $exception->error);
            $this->assertSame(
                [['field' => 'request', 'error' => 'Request must be a valid JSON object.']],
                $exception->getErrors()
            );
        });

        yield 'empty content' => [
            '',
            $invalidContentAsserts,
        ];

        yield 'invalid JSON' => [
            '{]',
            $invalidContentAsserts,
        ];

        yield 'invalid content' => [
            json_encode(42),
            $invalidContentAsserts,
        ];

        yield 'empty request' => [
            '[]',
            Closure::fromCallable(function (InvalidRequestException $exception) {
                $this->assertNull($exception->id);
                $this->assertSame(ServerError::requestError, $exception->error);
                $this->assertSame(
                    [['field' => 'request', 'error' => 'Request must contain all required fields.']],
                    $exception->getErrors()
                );
            }),
        ];
    }
}
