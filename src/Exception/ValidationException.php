<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationList;
use function Symfony\Component\String\u;

class ValidationException extends \Exception
{
    /** @var ConstraintViolationList */
    private ConstraintViolationList $violations;

    /**
     * @param ConstraintViolationList $violations
     */
    public function __construct(ConstraintViolationList $violations)
    {
        $this->violations = $violations;
        parent::__construct('Validation failed.');
    }

    /**
     * @return array[]
     */
    public function response()
    {
        $messages = [];
        foreach ($this->violations as $paramName => $violation) {
            $field = u($violation->getPropertyPath())
                ->replaceMatches('/[^A-Za-z0-9_]++/', '')
                ->toString();
            $messages[$field][] = $violation->getMessage();
        }
        return [
            'message' => $this->getMessage(),
            'errors' => $messages
        ];
    }
}
