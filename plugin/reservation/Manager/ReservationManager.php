<?php

namespace FormaLibre\ReservationBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Controller\ReservationController;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Entity\ResourceRights;
use FormaLibre\ReservationBundle\Entity\ResourceType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("formalibre.manager.reservation_manager")
 */
class ReservationManager
{
    private $om;
    private $tokenStorage;
    private $translator;
    private $em;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"   = @DI\Inject("translator"),
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        EntityManager $em,
        StrictDispatcher $eventDispatcher
    ) {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
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
        $event->setTitle($this->translator->trans('reservation', [], 'reservation').' - '.$reservation->getResource()->getName());
        $event->setIsEditable(false);

        return $event;
    }

    // Add to the jsonSerialize, some reservations fields
    public function completeJsonEventWithReservation(Reservation $reservation)
    {
        $color = $reservation->getResource()->getColor();

        return array_merge(
            $reservation->getEvent()->jsonSerialize(),
            [
                'color' => !empty($color) ? $color : '#3a87ad',
                'comment' => $reservation->getComment(),
                'resourceTypeId' => $reservation->getResource()->getResourceType()->getId(),
                'resourceTypeName' => $reservation->getResource()->getResourceType()->getName(),
                'resourceId' => $reservation->getResource()->getId(),
                'reservationId' => $reservation->getId(),
                'editable' => $this->hasAccess($reservation->getEvent()->getUser(), $reservation->getResource(), ReservationController::BOOK),
                'durationEditable' => $this->hasAccess($reservation->getEvent()->getUser(), $reservation->getResource(), ReservationController::BOOK),
            ]
        );
    }

    public function updateReservation(Reservation $reservation, $newStart, $newEnd)
    {
        $reservation->setStart($newStart);
        $reservation->setEnd($newEnd);

        $reservations = $this->em->getRepository('FormaLibreReservationBundle:Reservation')
            ->findByReservationDateAndResource($reservation, $newStart, $newEnd, $reservation->getResource());
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
            'role' => $role,
        ]);

        if (!$resourceRights) {
            $resourceRights = new ResourceRights();
            $resourceRights->setResource($resource);
            $resourceRights->setRole($role);

            $this->em->persist($resourceRights);
        }

        return $resourceRights;
    }

    public function hasAccess(User $user, Resource $resource, $mask)
    {
        $resourceRights = $resource->getResourceRights();
        $userRoles = $this->tokenStorage->getToken()->getRoles();

        $hasAccess = false;
        foreach ($userRoles as $userRole) {
            foreach ($resourceRights as $resourceRight) {
                if ($userRole->getRole() === $resourceRight->getRole()->getName() && $resourceRight->getMask() >= ReservationController::ADMIN) {
                    $hasAccess = true;
                    break;
                }

                if ($userRole->getRole() === $resourceRight->getRole()->getName() && $resourceRight->getMask() & $mask) {
                    if ((ReservationController::BOOK === $mask && $this->tokenStorage->getToken()->getUser() === $user) || ReservationController::BOOK !== $mask) {
                        $hasAccess = true;
                        break;
                    }
                }
            }
        }

        return $hasAccess;
    }

    public function checkAccess(User $user, Reservation $reservation, $mask)
    {
        if (!$this->hasAccess($user, $reservation->getResource(), $mask)) {
            throw new AccessDeniedException();
        }
    }

    public function deleteEventsBoundToResource(Resource $resource)
    {
        $genericData = new GenericDataEvent();
        $genericData->setData($resource);

        $this->eventDispatcher->dispatch(
            'formalibre_delete_event_from_resource',
            'GenericData',
            ['datas' => $genericData]
        );
    }

    public function deleteEventsBoundToResourcesType(ResourceType $resourceType)
    {
        foreach ($resourceType->getResources() as $resource) {
            $this->deleteEventsBoundToResource($resource);
        }
    }
}
