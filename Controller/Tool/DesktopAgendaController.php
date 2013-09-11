<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;

/**
 * Controller of the Agenda
 */
class DesktopAgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $om;
    private $request;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "translator"          = @DI\Inject("translator"),
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        Translator $translator
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->translator = $translator;
    }
    /**
     * @Route(
     *     "/show/",
     *     name="claro_desktop_agenda_show"
     * )
     */
    public function desktopShowAction()
    {
        $usr = $this->get('security.context')->getToken()->getUser();
        $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, 0);
        $desktopEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findDesktop();
        $data = array_merge($this->convertEventoArray($listEvents), $this->convertEventoArray($desktopEvents));

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route(
     *     "/add/",
     *     name="claro_desktop_agenda_add"
     * )
    */
    public function addEvent()
    {
        $event = new Event();
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            // the end date has to be bigger
            if ($event->getStart() <= $event->getEnd()) {
                $event->setUser($this->security->getToken()->getUser());
                $this->om->persist($event);
                $this->om->flush();
                $data = array(
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'start' => $event->getStart()->getTimestamp(),
                    'end' => $event->getEnd()->getTimestamp(),
                    'color' => $event->getPriority(),
                    'allDay' => $event->getAllDay()
                );

                return new Response(
                    json_encode($data),
                    200,
                    array('Content-Type' => 'application/json')
                );
            } else {
                return new Response(
                    json_encode(array('greeting' => ' start date is bigger than end date ')),
                    400,
                    array('Content-Type' => 'application/json')
                );
            }
        } else {
             return new Response(
                 json_encode(array('greeting' => 'dates are not valid')),
                 400,
                 array('Content-Type' => 'application/json')
             );
        }
    }

    /**
     * @Route(
     *     "/delete",
     *     name="claro_desktop_agenda_delete"
     * )
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction()
    {
        $repository = $this->om->getRepository('ClarolineCoreBundle:Event');
        $postData = $this->request->request->all();
        $event = $repository->find($postData['id']);
        $this->om->remove($event);
        $this->om->flush();

        return new Response(
            json_encode(array('greeting' => 'delete')),
            200,
            array('Content-Type' => 'application/json')
        );
    }

        /**
     * @EXT\Route(
     *     "/update",
     *     name="claro_desktop_agenda_update"
     * )
     * @EXT\Method("POST")
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction()
    {
        $postData = $this->request->request->all();
        $event = $this->om->getRepository('ClarolineCoreBundle:Event')->find($postData['id']);
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $this->om->flush();

            return new Response(
                json_encode(
                    array(
                        'id' => $event->getId(),
                        'title' => $event->getTitle(),
                        'start' => $event->getStart()->getTimestamp(),
                        'end' => $event->getEnd()->getTimestamp(),
                        'color' => $event->getPriority(),
                        'allDay' => $event->getAllDay(),
                        'description' => $event->getDescription()
                    )
                ),
                200,
                array('Content-Type' => 'application/json')
            );
        }

        return new Response(
            json_encode(
                array('dates are not valids')
            ),
            400,
            array('Content-Type' => 'application/json')
        );
    }

    private function convertEventoArray($listEvents)
    {
        $data = array();
        
        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $workspace = $object->getWorkspace();
            $data[$key]['title'] =  !is_null($workspace) ? $workspace->getName().': '.$object->getTitle() : $this->translator->trans('desktop', array(), 'platform');
            $data[$key]['allDay'] = $object->getAllDay();
            $data[$key]['start'] = $object->getStart()->getTimestamp();
            $data[$key]['end'] = $object->getEnd()->getTimestamp();
            $data[$key]['color'] = $object->getPriority();
            $data[$key]['visible'] = true;
        }

        return($data);
    }

}
