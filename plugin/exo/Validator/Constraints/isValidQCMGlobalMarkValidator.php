<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\Request;

class isValidQCMGlobalMarkValidator extends ConstraintValidator
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request->request->all();
    }
    public function validate($value, Constraint $constraint)
    {
        $interQCM = $this->request['ujm_exobundle_interactionqcmtype'];

        if (!isset($interQCM['weightResponse'])) {
            if (!preg_match('/^-?\d+(?:[.,]\d+)?$/', $value, $matches)) {
                $this->context->addViolation($constraint->message, array('%string%' => $value));
            }
        }
    }
}
