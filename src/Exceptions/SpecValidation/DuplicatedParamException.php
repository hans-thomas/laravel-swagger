<?php

namespace RonasIT\Support\AutoDoc\Exceptions\SpecValidation;

use Exception;

class DuplicatedParamException extends Exception
{
    public function __construct(string $in, string $name)
    {
        parent::__construct("Validation failed. Found multiple {$in} parameters named '{$name}'.");
    }
}
