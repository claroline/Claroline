<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use FormaLibre\ReservationBundle\Manager\ReservationManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("formalibre_duration_validator")
 */
class DurationValidator extends ConstraintValidator
{
    private $reservationManager;

    /**
     * @DI\InjectParams({
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager")
     * })
     */
    public function __construct(ReservationManager $reservationManager)
    {
        $this->reservationManager = $reservationManager;
    }

    public function validate($object, Constraint $constraint)
    {
        if ($object->getResource() && $object->getResource()->getMaxTimeReservation() !== '00:00:00' &&
            $this->reservationManager->convertTimeToTimestamp($object->getDuration()) >
            $this->reservationManager->convertTimeToTimestamp($object->getResource()->getMaxTimeReservation())) {
            $this->context->addViolation('valid_max_duration_required');
        }

        if (!preg_match('#[0-9]+:[0-9]{1,2}#', $object->getDuration())) {
            $this->context->addViolation('valid_duration_format_required');
        }
    }
}
