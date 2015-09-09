<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("reservation_modify_validator")
 */
class ReservationModifyValidator extends ConstraintValidator
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
                ->findByReservationDateAndResource($object, $object->getStartInTimestamp(), $object->getEndInTimestamp(), $object->getResource());
            $maxReservationAuthorized = $object->getResource()->getQuantity();

            if (count($reservations) >= $maxReservationAuthorized) {
                $this->context->addViolation('number_reservations_exceeded');
            }
        }
    }
}
