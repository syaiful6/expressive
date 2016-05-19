<?php

namespace App\Foundation\Http;

use App\Validation\ValidatorFactoryAwareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Contracts\Validation\Factory as FactoryContract;

abstract class BaseActionMiddleware implements ValidatorFactoryAwareInterface
{
    use DispatchMethod, ValidateRequest;

    /**
     *
     */
    public function setValidatorFactory(FactoryContract $factory)
    {
        $this->validationFactory = $factory;
    }
}
