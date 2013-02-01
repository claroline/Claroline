<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Controller of the calendar
 */
class CalendarController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
  
    public function addEventAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $event = new Event();
        $formBuilder = $this->createFormBuilder($event);
        $formBuilder->add('title', 'text')
            ->add(
                'end',
                'date',
                array(
                    'format' => 'dd-MM-yyyy',
                    'widget' => 'choice',
                )
            )
            ->add(
                'allDay',
                'checkbox',
                array(
                    'label' => 'all day ?')
            )
            ->add('description', 'textarea');
        $form = $formBuilder->getForm();
        $request = $this->get('request');

        if ($request->getMethod() === 'POST') {
            // get the value not send by the built in form
            $postData = $request->request->all();
            $form->bindRequest($request);

            if ($form->isValid()) {
                $date = explode('(', $postData['date']);
                $event->setStart(new \DateTime($date[0]));

                // the end date has to be bigger
                if ($event->getStart() <= $event->getEnd()) {
                    $event->setWorkspace($workspace);
                    $event->setUser($this->get('security.context')->getToken()->getUser());
                    $em->persist($event);
                    $em->flush();
                    $data = array(
                        'id' => $event->getId(),
                        'title' => $event->getTitle(),
                        'start' => date('Y-m-d', $event->getStart()),
                        'end' => date('Y-m-d', $event->getEnd())
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
            }

            return $this->render(
                'ClarolineCoreBundle:Tool:workspace/calendar/calendar.html.twig',
                array('workspace' => $workspace, 'form' => $form->createView())
            );
        }
    }

    public function showAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $listEvents = $workspace->getEvents();
        $data = array();

        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $data[$key]['title'] = $object->getTitle();
            $data[$key]['start'] = $object->getStart();
            $data[$key]['end'] = $object->getEnd();
        }

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    public function moveAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $request = $this->get('request');
        $postData = $request->request->all();
        $repository = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\Event');
        $event = $repository->find($postData['id']);
        // timestamp 1h = 3600
        $newStartDate = $event->getStart() + ((3600 * 24) * $postData['dayDelta']);
        $dateStart = new \DateTime(date('Y-m-d', $newStartDate));
        $event->setStart($dateStart);
        $newEndDate = $event->getEnd() + ((3600 * 24) * $postData['dayDelta']);
        $dateEnd = new \DateTime(date('Y-m-d', $newEndDate));
        $event->setStart($dateStart);
        $event->setEnd($dateEnd);
        $em->flush();

        return new Response(
            json_encode(
                array(
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'start' => $event->getStart(),
                    'end' => $event->getEnd()
                    )
            ),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    public function deleteAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkUserIsAllowed('calendar', $workspace);
        $repository = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\Event');
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

    public function DesktopShowAction()
    {
         $em = $this->getDoctrine()->getManager();
        $usr = $this-> get('security.context')-> getToken()-> getUser();
        $listEvents = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\Event')->getAllUserEvents($usr);

        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $data[$key]['title'] = $object->getTitle();
            $data[$key]['start'] = $object->getStart();
            $data[$key]['end'] = $object->getEnd();
        }

        return new Response(
            json_encode($data),
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
