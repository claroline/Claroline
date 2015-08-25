<?php

namespace FormaLibre\ReservationBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\ReservationBundle\Entity\Reservation;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "su"           = @DI\Inject("claroline.security.utilities"),
     *     "container"    = @DI\Inject("service_container")
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
        ContainerInterface $container
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
    }

    // Convert hh:mm to time in seconds
    public function convertTimeToTimestamp($duration)
    {
        $durationArray = explode(':', $duration);

        return $durationArray[0] * 3600 + $durationArray[1] * 60;
    }

    public function updateEvent(Event $event, Reservation $reservation)
    {
        $event->setStart($reservation->getStart());
        $event->setEnd($reservation->getEnd());
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
}