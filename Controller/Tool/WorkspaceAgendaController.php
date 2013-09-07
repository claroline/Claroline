<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the agenda
 */
class WorkspaceAgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $om;
    private $request;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request")
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/add",
     *     name="claro_workspace_agenda_add_event"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEventAction(AbstractWorkspace $workspace)
    {
        $this->checkUserIsAllowed('agenda', $workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $form->getData();
            // the end date has to be bigger
            if ($event->getStart() <= $event->getEnd()) {
                $event->setWorkspace($workspace);
                $event->setUser($this->security->getToken()->getUser());
                $this->om->persist($event);
                $this->om->flush();
                $data = array(
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'start' => $event->getStart()->getTimestamp(),
                    'end' => $event->getEnd()->getTimestamp(),
                    'color' => $event->getPriority(),
                    'allDay' => $event->getAllDay(),
                    'description' => $event->getDescription()
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

            return new Response(
                json_encode(array('greeting' => 'dates are not valid')),
                400,
                array('Content-Type' => 'application/json')
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/update",
     *     name="claro_workspace_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(AbstractWorkspace $workspace)
    {
        $this->checkUserIsAllowed('agenda', $workspace);
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
            200,
             array('Content-Type' => 'application/json')
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/delete",
     *     name="claro_workspace_agenda_delete"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(AbstractWorkspace $workspace)
    {

        $this->checkUserIsAllowed('agenda', $workspace);
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
     *     "/{workspaceId}/show",
     *     name="claro_workspace_agenda_show"
     * )
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(AbstractWorkspace $workspace)
    {

        $this->checkUserIsAllowed('agenda', $workspace);
        $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')
            ->findbyWorkspaceId($workspace->getId(), false);
        $data = array();

        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $data[$key]['title'] = $object->getTitle();
            $data[$key]['allDay'] = $object->getAllDay();
            $data[$key]['start'] = $object->getStart()->getTimestamp();
            $data[$key]['end'] = $object->getEnd()->getTimestamp();
            $data[$key]['color'] = $object->getPriority();
            $data[$key]['description'] = $object->getDescription();
        }

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @EXT\Route(
     *     "/move",
     *     name="claro_workspace_agenda_move"
     * )
     *  @param Event $event
     */
    public function moveAction()
    {

        $postData = $this->request->request->all();
        $repository = $this->om->getRepository('ClarolineCoreBundle:Event');
        $event = $repository->find($postData['id']);
        $this->checkUserIsAllowed('agenda', $event->getWorkspace());
        // timestamp 1h = 3600
        $newStartDate = $event->getStart()->getTimestamp() + ((3600 * 24) * $postData['dayDelta']);
        $dateStart = new \DateTime(date('d-m-Y H:i', $newStartDate));
        $event->setStart($dateStart);
        $newEndDate = $event->getEnd()->getTimestamp() + ((3600 * 24) * $postData['dayDelta']);
        $dateEnd = new \DateTime(date('d-m-Y H:i', $newEndDate));
        $event->setStart($dateStart);
        $event->setEnd($dateEnd);
        $this->om->flush();

        return new Response(
            json_encode(
                array(
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'allDay' => $event->getAllDay(),
                    'start' => $event->getStart()->getTimestamp(),
                    'end' => $event->getEnd()->getTimestamp(),
                    'color' => $event->getPriority()
                    )
            ),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    private function checkUserIsAllowed($permission, AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
