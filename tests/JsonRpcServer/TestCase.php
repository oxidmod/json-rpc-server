<?php
declare(strict_types=1);

namespace Oxidmod\Tests\JsonRpcServer;

use Oxidmod\JsonRpcServer\Request\Request;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const REQUEST_DATA = [
        'jsonrpc' => '2.0',
        'id' => 42,
        'method' => 'request_method',
        'params' => [
            'param_1' => 13,
        ],
    ];

    protected function createRequest(array $overrides = []): Request
    {
        return new Request(array_merge([], self::REQUEST_DATA, $overrides));
    }
}
