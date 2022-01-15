<?php

declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer\Request;

use Oxidmod\JsonRpcServer\Request\BatchRequest;
use Oxidmod\JsonRpcServer\Request\InvalidRequestException;
use Oxidmod\JsonRpcServer\Request\Request;
use Oxidmod\Tests\JsonRpcServer\TestCase;

class BatchRequestTest extends TestCase
{
    public function testRequests(): void
    {
        $data = [
            ['jsonrpc' => '2.0', 'id' => 42, 'method' => 'test'],
            ['jsonrpc' => '2.0', 'id' => 43, 'method' => 13],
        ];

        $request = new BatchRequest($data);

        $this->assertEquals(
            $this->prepareExpectedResult($data),
            iterator_to_array($request->requests())
        );
    }

    private function prepareExpectedResult(array $data): array
    {
        $result = [];
        foreach ($data as $request) {
            try {
                $result[] = new Request($request);
            } catch (InvalidRequestException $exception) {
                $result[] = $exception;
            }
        }

        return $result;
    }
}
