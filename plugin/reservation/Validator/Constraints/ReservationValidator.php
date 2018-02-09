<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("reservation_validator")
 */
class ReservationValidator extends ConstraintValidator
{
    private $em;

    /**
     * @DI\InjectParams({
     *      "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($object, Constraint $constraint)
    {
        if ($object->getResource()) {
            $reservations = $this->em->getRepository('FormaLibreReservationBundle:Reservation')
                ->findByDateAndResource($object->getStartInTimestamp(), $object->getEndInTimestamp(), $object->getResource());
            $maxReservationAuthorized = $object->getResource()->getQuantity();

            if (count($reservations) >= $maxReservationAuthorized) {
                $this->context->addViolation('number_reservations_exceeded');
            }
        }
    }
}
