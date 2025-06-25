<?php

namespace App\Exceptions;

class TransException extends \Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct($lanMessage, $attributes = [])
    {
        parent::__construct(trans("lang." . $lanMessage, $attributes));
    }
}
