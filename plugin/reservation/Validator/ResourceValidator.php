<?php

namespace FormaLibre\ReservationBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.validator")
 */
class ResourceValidator implements ValidatorInterface
{
    public function validate($data)
    {
        $errors = [];

        if (!isset($data['name']) || empty($data['name'])) {
            $errors[] = [
                'path' => 'name',
                'message' => 'The name cannot be empty.',
            ];
        }

        return $errors;
    }

    public function validateBulk(array $users)
    {
    }

    public function getUniqueFields()
    {
        return [];
    }

    public function getClass()
    {
        return 'FormaLibre\ReservationBundle\Entity\Resource';
    }
}
