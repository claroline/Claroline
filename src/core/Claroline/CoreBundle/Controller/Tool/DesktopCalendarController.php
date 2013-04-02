<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the calendar
 */
class DesktopCalendarController extends Controller{

    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';

    /**
     * @Route(
     *     "/show/",
     *     name="claro_desktop_calendar_show"
     * )
     */
    public function desktopShowAction()
    {
        $em = $this->getDoctrine()->getManager();
        $usr = $this-> get('security.context')-> getToken()-> getUser();
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, 0);
        $data = array();
        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $workspace = $object->getWorkspace();
            $data[$key]['title'] = $workspace->getName().': '.$object->getTitle();
            $data[$key]['allDay'] = $object->getAllDay();
            $data[$key]['start'] = $object->getStart();
            $data[$key]['end'] = $object->getEnd();
            $data[$key]['color'] = $object->getPriority();
        }

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }
}