<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Event\LogUserUpdateEvent;

/**
 * Controller of the user profile.
 */
class LogController extends Controller
{
    /**
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

        return $this->render(
            'ClarolineCoreBundle:Log:view_details.html.twig',
            array('log' => $log)
        );
    }
}