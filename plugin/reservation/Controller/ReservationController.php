<?php

namespace FormaLibre\ReservationBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Doctrine\Common\Persistence\ObjectManager;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Manager\ReservationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReservationController extends Controller
{
    const SEE = 1;
    const BOOK = 2;
    const ADMIN = 4;

    private $om;
    private $router;
    private $request;
    private $agendaManager;
    private $reservationManager;
    private $translator;
    private $tokenStorage;
    private $reservationRepo;
    private $eventRepo;

    /**
     * @DI\InjectParams({
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"      = @DI\Inject("router"),
     *      "request"     = @DI\Inject("request"),
     *      "agendaManager" = @DI\Inject("claroline.manager.agenda_manager"),
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager"),
     *      "translator"    = @DI\Inject("translator"),
     *      "tokenStorage"  = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        ObjectManager $om,
        RouterInterface $router,
        Request $request,
        AgendaManager $agendaManager,
        ReservationManager $reservationManager,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->router = $router;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->reservationManager = $reservationManager;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->reservationRepo = $this->om->getRepository('FormaLibreReservationBundle:Reservation');
        $this->eventRepo = $this->om->getRepository('ClarolineAgendaBundle:Event');
    }

    /**
     * @EXT\Route(
     *      "/agenda/show",
     *      name="formalibre_reservation_agenda_show",
     *      options={"expose"=true}
     * )
     */
    public function getReservationsAction()
    {
        $reservations = $this->reservationRepo->findAll();

        $events = [];
        foreach ($reservations as $reservation) {
            if ($this->reservationManager->hasAccess($reservation->getEvent()->getUser(), $reservation->getResource(), $this::SEE)) {
                $events[] = $this->reservationManager->completeJsonEventWithReservation($reservation);
            }
        }

        return new JsonResponse($events);
    }

    /**
     * @EXT\Route(
     *      "/add",
     *      name="formalibre_add_reservation",
     *      options={"expose"=true}
     * )
     */
    public function addReservationAction()
    {
        $formType = $this->get('formalibre.form.reservation');
        $form = $this->createForm($formType, new Reservation());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $reservation = $form->getData();
            $this->reservationManager->checkAccess($this->tokenStorage->getToken()->getUser(), $reservation, $this::BOOK);

            $event = $this->reservationManager->updateEvent(new Event(), $reservation);
            $this->agendaManager->addEvent($event);

            $reservation->setEvent($event);
            $this->om->persist($reservation);
            $this->om->flush();

            return new JsonResponse($this->reservationManager->completeJsonEventWithReservation($reservation));
        }

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', [
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_add_reservation'),
            'reservation' => $this->request->getMethod() === 'POST' ? $form->getData() : null,
            'editMode' => false,
        ]);
    }

    /**
     * @EXT\Route(
     *      "/change/form/{id}",
     *      name="formalibre_change_reservation_form",
     *      options={"expose"=true}
     * )
     */
    public function changeReservationFormAction(Reservation $reservation)
    {
        $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

        $formType = $this->get('formalibre.form.reservation');
        $formType->setEditMode();
        $reservation->setStart($reservation->getEvent()->getStartInTimestamp());
        $reservation->setEnd($reservation->getEvent()->getEndInTimestamp());
        $form = $this->createForm($formType, $reservation);

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', [
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_change_reservation', ['id' => $reservation->getId()]),
            'reservation' => $reservation,
            'editMode' => true,
            'canDelete' => $this->reservationManager->hasAccess($reservation->getEvent()->getUser(), $reservation->getResource(), self::BOOK),
        ]);
    }

    /**
     * @EXT\Route(
     *      "/change/{id}",
     *      name="formalibre_change_reservation",
     *      options={"expose"=true}
     * )
     */
    public function changeReservationAction(Reservation $reservation)
    {
        $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

        $formType = $this->get('formalibre.form.reservation');
        $formType->setEditMode();
        $form = $this->createForm($formType, $reservation);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $reservation = $form->getData();
            $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

            $event = $this->reservationManager->updateEvent($reservation->getEvent(), $reservation);

            $this->agendaManager->updateEvent($event);
            $this->om->flush();

            return new JsonResponse($this->reservationManager->completeJsonEventWithReservation($reservation));
        }

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', [
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_change_reservation', ['id' => $reservation->getId()]),
            'reservation' => $reservation,
            'editMode' => true,
            'canDelete' => $this->reservationManager->hasAccess($reservation->getEvent()->getUser(), $reservation->getResource(), self::BOOK),
        ]);
    }

    /**
     * @EXT\Route(
     *      "/{id}/move/{minutes}",
     *      name="formalibre_reservation_move",
     *      options={"expose"=true}
     * )
     */
    public function moveReservationAction(Reservation $reservation, $minutes)
    {
        $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

        $newStart = $reservation->getEvent()->getStartInTimestamp() + $minutes * 60;
        $newEnd = $reservation->getEvent()->getEndInTimestamp() + $minutes * 60;

        return $this->reservationManager->updateReservation($reservation, $newStart, $newEnd);
    }

    /**
     * @EXT\Route(
     *      "/{id}/resize/{minutes}",
     *      name="formalibre_resize_reservation",
     *      options={"expose"=true}
     * )
     */
    public function resizeReservationAction(Reservation $reservation, $minutes)
    {
        $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

        $start = $reservation->getEvent()->getStartInTimestamp();
        $newEnd = $reservation->getEvent()->getEndInTimestamp() + $minutes * 60;
        $maxTimeArray = explode(':', $reservation->getResource()->getMaxTimeReservation());
        $maxTime = $maxTimeArray[0] * 3600 + $maxTimeArray[2] * 60;

        if ($newEnd - $start > $maxTime && $maxTime !== 0) {
            return new JsonResponse(['error' => 'error.max_time_reservation_exceeded']);
        }

        return $this->reservationManager->updateReservation($reservation, $start, $newEnd);
    }

    /**
     * @ext\Route(
     *      "/delete/{id}",
     *      name="formalibre_delete_reservation",
     *      options={"expose"=true}
     * )
     */
    public function deleteReservationAction(Reservation $reservation)
    {
        $this->reservationManager->checkAccess($reservation->getEvent()->getUser(), $reservation, $this::BOOK);

        $this->om->remove($reservation);
        $this->om->flush();

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *      "/get-resource-info/{id}",
     *      name="formalibre_reservation_get_resource_info",
     *      defaults={"id"=null},
     *      options={"expose"=true}
     * )
     */
    public function getResourceInfoAction(Resource $resource = null)
    {
        $none = $this->translator->trans('none', [], 'platform');

        if (!$resource) {
            return new JsonResponse([
                'description' => $none,
                'localisation' => $none,
                'maxTime' => $none,
            ]);
        }

        $description = $resource->getDescription();
        $localisation = $resource->getLocalisation();

        return new JsonResponse([
            'description' => empty($description) ? $none : $description,
            'localisation' => empty($localisation) ? $none : $localisation,
            'maxTime' => $resource->getMaxTimeReservation() === '00:00:00' ? $none : $resource->getMaxTimeReservation(),
        ]);
    }
}
