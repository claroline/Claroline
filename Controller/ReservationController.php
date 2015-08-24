<?php

namespace FormaLibre\ReservationBundle\Controller;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ReservationController extends Controller
{
    private $em;
    private $om;
    private $formFactory;
    private $router;
    private $request;
    private $reservationRepo;
    private $eventRepo;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"      = @DI\Inject("router"),
     *      "request"     = @DI\Inject("request")
     * })
     */
    public function __construct(EntityManager $em, FormFactory $formFactory, ObjectManager $om, RouterInterface $router, Request $request)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
        $this->request = $request;
        $this->reservationRepo = $this->om->getRepository('FormaLibreReservationBundle:Reservation');
        $this->eventRepo = $this->om->getRepository('ClarolineAgendaBundle:Event');
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
            //$form->get('property')->getData()
            return new JsonResponse();
        }

        return $this->render('FormaLibreReservationBundle:Tool:addReservationForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_add_reservation'),
            'editMode' => false
        ));
    }
}