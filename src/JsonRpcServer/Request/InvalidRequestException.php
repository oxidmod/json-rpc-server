<?php
declare(strict_types=1);

namespace Oxidmod\JsonRpcServer\Request;

use Oxidmod\JsonRpcServer\ServerError;
use InvalidArgumentException;

class InvalidRequestException extends InvalidArgumentException
{
    /** @var string[][] */
    private array $errors = [];

    public function __construct(
        public readonly int|string|null $id,
        public readonly ServerError $error
    ) {
        parent::__construct(
            $error->message(),
            $error->value
        );
    }

    /**
     * @return string[][]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function addError(string $field, string $error): self
    {
        $this->errors[] = compact('field', 'error');
        return $this;
    }
}
