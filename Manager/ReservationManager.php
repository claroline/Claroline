<?php

namespace FormaLibre\ReservationBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Entity\Reservation;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Library\Security\Utilities;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Service("formalibre.manager.reservation_manager")
 */
class ReservationManager
{
    private $om;
    private $tokenStorage;
    private $authorization;
    private $rm;
    private $translator;
    private $su;
    private $container;
    private $em;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "su"           = @DI\Inject("claroline.security.utilities"),
     *     "container"    = @DI\Inject("service_container"),
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        RoleManager $rm,
        TranslatorInterface $translator,
        Utilities $su,
        ContainerInterface $container,
        EntityManager $em
    )
    {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->rm = $rm;
        $this->translator = $translator;
        $this->su = $su;
        $this->container = $container;
        $this->em = $em;
    }

    // Convert hh:mm to time in seconds
    public function convertTimeToTimestamp($duration)
    {
        $durationArray = explode(':', $duration);

        return $durationArray[0] * 3600 + $durationArray[1] * 60;
    }

    public function updateEvent(Event $event, Reservation $reservation)
    {
        $event->setStart($reservation->getStartInTimestamp());
        $event->setEnd($reservation->getEndInTimestamp());
        $event->setTitle($this->translator->trans('reservation', [], 'reservation') .' - '. $reservation->getResource()->getName());

        return $event;
    }

    // Add to the jsonSerialize, some reservations fields
    public function completeJsonEventWithReservation(Reservation $reservation)
    {
        return array_merge(
            $reservation->getEvent()->jsonSerialize(),
            [
                'resourceTypeId' => $reservation->getResource()->getResourceType()->getId(),
                'resourceTypeName' => $reservation->getResource()->getResourceType()->getName(),
                'resourceId' => $reservation->getResource()->getId(),
                'reservationId' => $reservation->getId(),
                'editable' => $reservation->getEvent()->getUser() === $this->tokenStorage->getToken()->getUser(),
                'durationEditable' => $reservation->getEvent()->getUser() === $this->tokenStorage->getToken()->getUser()
            ]
        );
    }

    public function updateReservation(Reservation $reservation, $newStart, $newEnd)
    {
        $reservation->setStart($newStart);
        $reservation->setEnd($newEnd);

        $reservations = $this->em->getRepository('FormaLibreReservationBundle:Reservation')->findByDateAndResource($reservation, true);
        if (count($reservations) >= $reservation->getResource()->getQuantity()) {
            return new JsonResponse(['error' => 'error.number_reservations_exceeded']);
        }

        $event = $reservation->getEvent();
        $event->setStart($newStart);
        $event->setEnd($newEnd);
        $this->om->flush();

        return new JsonResponse($this->completeJsonEventWithReservation($reservation));
    }
}