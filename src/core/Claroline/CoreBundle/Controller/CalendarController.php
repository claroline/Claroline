<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Workspace\Event;

Class CalendarController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';

    public function indexAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        $event = new Event();
        $formBuilder = $this->createFormBuilder($event);
        $formBuilder
            ->add('title', 'text', array('required' => true))
            ->add('end', 'date', array(
                'format' => 'dd-MM-yyyy',
                'widget' => 'choice',
                'data' => new \DateTime('now')
            ))
            ->add('description', 'textarea');
        $form = $formBuilder->getForm();

        return $this->render(
            'ClarolineCoreBundle:Workspace:tools/calendar.html.twig', 
            array('workspace' => $workspace, 'form' => $form->createView())
        );
    }

    public function addDateAction($workspaceId)
    {
        $event = new Event();
        $formBuilder = $this->createFormBuilder($event);
        $formBuilder->add('title', 'text')
            ->add('end', 'date', array(
                'format' => 'dd-MM-yyyy',
                'widget' => 'choice',
            ))
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
                if ($event->getStart() < $event->getEnd()) {
                    $em = $this->getDoctrine()->getManager();
                    $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
                    $event->setWorkspace($workspace);
                    $event->setUser($this->get('security.context')->getToken()->getUser());
                    $em->persist($event);
                    $em->flush();
                    $return = array(
                        "responseCode" => 200,
                        "greeting" => "ok",
                        "title" => $event->getTitle(),
                        "start" => date('Y-m-d', $event->getStart()),
                        "end" => date('Y-m-d', $event->getEnd())
                    );
                } else {
                    $return = array("responseCode" => 400, "greeting" => " start date is bigger than end date ");
                }
            } else {
                $return = array("responseCode" => 400, "greeting" => "not valid");
            }
        }
        else
            $return = array("responseCode" => 400, "greeting" => "no post");

        $return = json_encode($return); //jscon encode the array
        
        return new Response($return, 200, array('Content-Type' => 'application/json')); //make sure it has the correct content type
    }

    public function showAction($workspaceId)
    {
         $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $listEvents = $workspace->getEvents();
        $data = array();
        
        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $data[$key]['title'] = $object->getTitle();
            $data[$key]['start'] = $object->getStart();
            $data[$key]['end'] = $object->getEnd();
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:tools/calendar_json.html.twig',
            array('data' => utf8_encode(json_encode($data)))
        );
    }

    public function moveAction()
    {
        $request = $this->get('request');
        $postData = $request->request->all();
        $em = $this->getDoctrine()->getEntityManager();
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

        $return = json_encode(array("responseCode" => 200, "greeting" => "ok")); //jscon encode the array
        
        return new Response(
            json_encode(array("responseCode" => 200, "greeting" => "ok")), 
            200, 
            array('Content-Type' => 'application/json')
        );
    }

    private function checkRegistration($workspace)
    {
        foreach ($workspace->getWorkspaceRoles() as $role) {
            if ($this->get('security.context')->isGranted($role->getName())) {
                return true;
            }
        }

        throw new AccessDeniedHttpException();
    }
}