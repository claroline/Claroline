<?php

namespace FormaLibre\ReservationBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\GenericDatasEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Controller\ReservationController;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Entity\ResourceRights;
use FormaLibre\ReservationBundle\Entity\ResourceType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    private $eventDispatcher;

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
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher")
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
        EntityManager $em,
        StrictDispatcher $eventDispatcher
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
        $this->eventDispatcher = $eventDispatcher;
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
        $event->setIsEditable(false);

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
                'editable' => $this->hasAccess($reservation->getResource(), ReservationController::EDIT),
                'durationEditable' => $this->hasAccess($reservation->getResource(), ReservationController::EDIT)
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

    public function getResourceRightsByRoleAndResource(Resource $resource, Role $role)
    {
        $resourceRights = $this->em->getRepository('FormaLibreReservationBundle:ResourceRights')->findOneBy([
            'resource' => $resource,
            'role' => $role
        ]);

        if (!$resourceRights) {
            $resourceRights = new ResourceRights();
            $resourceRights->setResource($resource);
            $resourceRights->setRole($role);

            $this->em->persist($resourceRights);
        }

        return $resourceRights;
    }

    public function hasAccess(Resource $resource, $mask)
    {
        $resourceRights = $resource->getResourceRights();
        $userRoles = $this->tokenStorage->getToken()->getRoles();

        $hasAccess = false;
        foreach ($userRoles as $userRole) {
            foreach ($resourceRights as $resourceRight) {
                if ($userRole->getRole() == $resourceRight->getRole()->getName() && $resourceRight->getMask() & $mask) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        return $hasAccess;
    }

    public function checkAccess(Reservation $reservation, $mask)
    {
        if (!$this->hasAccess($reservation->getResource(), $mask)) {
            throw new AccessDeniedException();
        }
    }

    public function deleteEventsBoundToResource(Resource $resource)
    {
        $genericDatas = new GenericDatasEvent();
        $genericDatas->setDatas($resource);

        $this->eventDispatcher->dispatch(
            'formalibre_delete_event_from_resource',
            'GenericDatas',
            ['datas' => $genericDatas]
        );
    }

    public function deleteEventsBoundToResourcesType(ResourceType $resourceType)
    {
        foreach ($resourceType->getResources() as $resource) {
            $this->deleteEventsBoundToResource($resource);
        }
    }
}