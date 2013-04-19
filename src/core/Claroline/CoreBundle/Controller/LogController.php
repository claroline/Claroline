<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Event\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Event\LogResourceChildUpdateEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the user profile.
 */
class LogController extends Controller
{
    /**
     * @Route(
     *     "/view_details/{logId}",
     *     name="claro_log_view_details",
     *     options={"expose"=true}
     * )
     *
     * Displays the public profile of an user.
     *
     * @param integer $userId The id of the user we want to see the profile
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewDetailsAction($logId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $log = $em->getRepository('ClarolineCoreBundle:Logger\Log')->find($logId);

        if ($log->getAction() === LogResourceChildUpdateEvent::ACTION ) {
            $eventName = 'create_log_details_'.$log->getResourceType()->getName();
            $event = new LogCreateDelegateViewEvent($log);
            $this->container->get('event_dispatcher')->dispatch($eventName, $event);

            if ($event->getResponseContent() === "") {
                throw new \Exception(
                    "Event '{$eventName}' didn't receive any response."
                );
            }

            return new Response($event->getResponseContent());
        }

        return $this->render(
            'ClarolineCoreBundle:Log:view_details.html.twig',
            array('log' => $log)
        );
    }
}