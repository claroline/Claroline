<?php

namespace FormaLibre\ReservationBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Manager\ReservationManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReservationController extends Controller
{
    private $em;
    private $om;
    private $formFactory;
    private $router;
    private $request;
    private $agendaManager;
    private $reservationManager;
    private $translator;
    private $reservationRepo;
    private $eventRepo;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"      = @DI\Inject("router"),
     *      "request"     = @DI\Inject("request"),
     *      "agendaManager" = @DI\Inject("claroline.manager.agenda_manager"),
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager"),
     *      "translator"    = @DI\Inject("translator")
     * })
     */
    public function __construct(
        EntityManager $em,
        FormFactory $formFactory,
        ObjectManager $om,
        RouterInterface $router,
        Request $request,
        AgendaManager $agendaManager,
        ReservationManager $reservationManager,
        TranslatorInterface $translator
    )
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->reservationManager = $reservationManager;
        $this->translator = $translator;
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
    public function agendaShowAction()
    {
        $reservations = $this->reservationRepo->findAll();

        $events = [];
        foreach ($reservations as $reservation) {
            $events[] = $this->reservationManager->completeJsonEventWithReservation($reservation);
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

            $event = $this->reservationManager->updateEvent(new Event(), $reservation);
            $this->agendaManager->addEvent($event);

            $reservation->setEvent($event);
            $this->om->persist($reservation);
            $this->om->flush();

            return new JsonResponse($this->reservationManager->completeJsonEventWithReservation($reservation));
        }

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_add_reservation'),
            'reservation' => $this->request->getMethod() === 'POST' ? $form->getData() : null,
            'editMode' => false
        ));
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
        $formType = $this->get('formalibre.form.reservation');
        $formType->setEditMode();
        $reservation->setStart($reservation->getEvent()->getStart()->getTimestamp());
        $reservation->setEnd($reservation->getEvent()->getEnd()->getTimestamp());
        $form = $this->createForm($formType, $reservation);

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_change_reservation', ['id' => $reservation->getId()]),
            'reservation' => $reservation,
            'editMode' => true
        ));
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
        $formType = $this->get('formalibre.form.reservation');
        $form = $this->createForm($formType, $reservation);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $reservation = $form->getData();
            $event = $this->reservationManager->updateEvent($reservation->getEvent(), $reservation);

            $this->agendaManager->updateEvent($event);
            $this->om->flush();

            return new JsonResponse($this->reservationManager->completeJsonEventWithReservation($reservation));
        }

        return $this->render('FormaLibreReservationBundle:Tool:reservationForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_change_reservation', ['id' => $reservation->getId()]),
            'reservation' => $reservation,
            'editMode' => true
        ));
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
                'maxTime' => $none
            ]);
        }

        return new JsonResponse([
            'description' => empty($resource->getDescription()) ? $none : $resource->getDescription(),
            'localisation' => empty($resource->getLocalisation()) ? $none : $resource->getLocalisation(),
            'maxTime' => $resource->getMaxTimeReservation() === '00:00:00' ? $none : $resource->getMaxTimeReservation()
        ]);
    }
}