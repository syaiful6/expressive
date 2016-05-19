<?php

namespace App\Validation;

use Illuminate\Contracts\Validation\Factory as FactoryContract;

interface ValidatorFactoryAwareInterface
{
    /**
     *
     */
    public function setValidatorFactory(FactoryContract $factory);
}
