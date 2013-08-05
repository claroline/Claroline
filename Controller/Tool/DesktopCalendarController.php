<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the calendar
 */
class DesktopCalendarController extends Controller
{
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
        $data = $this->convertEventoArray($listEvents);

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    private function convertEventoArray($listEvents)
    {
        $data = array();
        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $workspace = $object->getWorkspace();
            $data[$key]['title'] = $workspace->getName().': '.$object->getTitle();
            $data[$key]['allDay'] = $object->getAllDay();
            $data[$key]['start'] = $object->getStart()->getTimestamp();
            $data[$key]['end'] = $object->getEnd()->getTimestamp();
            $data[$key]['color'] = $object->getPriority();
            $data[$key]['visible'] = true;
        }

        return($data);
    }
}
