<?php

namespace App\Exceptions;

class NoActiveSubscription extends \Exception
{
    public $subscriptionCode;
    /**
     * Create a new class instance.
     */
    public function __construct($lanMessage, $attributes = [], array $subscriptionCode = null)
    {
        parent::__construct(trans("lang." . $lanMessage, $attributes));
        $this->subscriptionCode = $subscriptionCode;
    }
}
