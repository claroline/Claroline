<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\CalendarType;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Controller of the calendar
 */
class WorkspaceCalendarController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';

    /**
     * @Route(
     *     "/{workspaceId}/add",
     *     name="claro_workspace_agenda_add_event"
     * )
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEventAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $event = new Event();
        $form = $this->createForm(new CalendarType, $event);
        $request = $this->get('request');

        if ($request->getMethod() === 'POST') {
            // get the value not send by the built in form
            $postData = $request->request->all();
            $form->bind($request);
            if ($form->isValid()) {

                $date = explode('(', $postData['date']);
                $event->setStart($date[0]);

                // the end date has to be bigger
                if ($event->getStart() <= $event->getEnd()) {
                    $event->setWorkspace($workspace);
                    $event->setUser($this->get('security.context')->getToken()->getUser());
                    $em->persist($event);
                    $em->flush();
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
                $error = $form->getErrors();
                foreach ($error as $value) {
                 }
                 return new Response(
                        json_encode(array('greeting' => 'form invalid')),
                        400,
                        array('Content-Type' => 'application/json')
                    );
            }
        }
    }

    /**
     * @Route(
     *     "/{workspaceId}/update",
     *     name="claro_workspace_agenda_update"
     * )
     * @Method("POST")
     *
     * @param type $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $request = $this->get('request');
        $postData = $request->request->all();
        $event = $em->getRepository('ClarolineCoreBundle:Event')->find($postData['id']);
        $form = $this->createForm(new CalendarType(), $event);

        if ($request->getMethod() === 'POST') {

            $form->bind($request);

            if ($form->isValid()) {

                $em->persist($event);
                $em->flush();
            }

            return new Response(
                json_encode(
                    array(
                        'id' => $event->getId(),
                        'title' => $event->getTitle(),
                        'start' => $event->getStart()->getTimestamp(),
                        'end' => $event->getEnd()->getTimestamp(),
                        'color' => $event->getPriority()
                    )
                ),
                200,
                array('Content-Type' => 'application/json')
            );
        }
    }

    /**
     * @Route(
     *     "/{workspaceId}/delete",
     *     name="claro_workspace_agenda_delete"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $repository = $em->getRepository('ClarolineCoreBundle:Event');
        $request = $this->get('request');
        $postData = $request->request->all();
        $event = $repository->find($postData['id']);
        $em->remove($event);
        $em->flush();

        return new Response(
            json_encode(array('greeting' => 'delete')),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/show",
     *     name="claro_workspace_agenda_show"
     * )
     * @Method({"GET","POST"})
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findbyWorkspaceId($workspaceId, false);
        $data = array();
        foreach ($listEvents as $key => $object) {

                $data[$key]['id'] = $object->getId();
                $data[$key]['title'] = $object->getTitle();
                $data[$key]['allDay'] = $object->getAllDay();
                $data[$key]['start'] = $object->getStart()->getTimestamp();
                $data[$key]['end'] = $object->getEnd()->getTimestamp();
                $data[$key]['color'] = $object->getPriority();
        }

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route(
     *     "/move",
     *     name="claro_workspace_agenda_move"
     * )
     */
    public function moveAction()
    {

        $request = $this->get('request');
        $postData = $request->request->all();
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Event');
        $event = $repository->find($postData['id']);
        $this->checkUserIsAllowed('calendar', $event->getWorkspace());
        // timestamp 1h = 3600
        $newStartDate = $event->getStart()->getTimestamp() + ((3600 * 24) * $postData['dayDelta']);
        $dateStart = new \DateTime(date('d-m-Y', $newStartDate));
        $event->setStart($dateStart);
        $newEndDate = $event->getEnd()->getTimestamp() + ((3600 * 24) * $postData['dayDelta']);
        $dateEnd = new \DateTime(date('d-m-Y', $newEndDate));
        $event->setStart($dateStart);
        $event->setEnd($dateEnd);
        $em->flush();

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
        if (!$this->get('security.context')->isGranted($permission, $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }
}
