<?php

namespace App\Foundation\Http;

use RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

trait ValidateRequest
{
    /**
     *
     */
    public function isValid(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        $validator = $this->getValidationFactory()->make(
            $this->getAllRequestInput($request),
            $rules,
            $messages,
            $customAttributes
        );

        return !$validator->fails();
    }

    /**
     *
     */
    protected function getAllRequestInput(Request $request)
    {
        $source = $request->getParsedBody() + $request->getQueryParams();
        $files = $request->getUploadedFiles();

        return array_replace_recursive($source, $files);
    }

    /**
     * Return \Illuminate\Contracts\Validation\Factory|false
     */
    protected function getValidationFactory()
    {
        if (property_exists($this, 'validationFactory')) {
            return $this->validationFactory;
        }

        throw new RuntimeException('need the validation factory for validation.');
    }
}
