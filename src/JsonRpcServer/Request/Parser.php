<?php

declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Request;

use Oxidmod\JsonRpcServer\ServerError;

class Parser
{
    public function parseRawContent(string $content): Request|BatchRequest
    {
        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw (new InvalidRequestException(null, ServerError::parseError))
                ->addError('request', 'Request must be a valid JSON object.');
        }

        if (empty($data)) {
            throw (new InvalidRequestException(null, ServerError::requestError))
                ->addError('request', 'Request must contain all required fields.');
        }

        return array_is_list($data) ? new BatchRequest($data) : new Request($data);
    }
}
