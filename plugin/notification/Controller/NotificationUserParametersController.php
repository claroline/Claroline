<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/8/15
 */

namespace Icap\NotificationBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationUserParametersController extends Controller
{
    /**
     * @Route("/parameters", name="icap_notification_user_parameters")
     * @Method({"GET"})
     * @Template("IcapNotificationBundle:Parameters:config.html.twig")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getAction(User $user)
    {
        $parametersManager = $this->getParametersManager();
        $parameters = $parametersManager->getParametersByUser($user);
        $types = $parametersManager->allTypesList($parameters);

        return ['types' => $types, 'rssId' => $parameters->getRssId()];
    }

    /**
     * @Route("/parameters", name="icap_notification_save_user_parameters")
     * @Method({"POST"})
     * @Template("IcapNotificationBundle:Parameters:config.html.twig")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $user)
    {
        $this->getParametersManager()->processUpdate($request->request->all(), $user);

        return new RedirectResponse($this->generateUrl('claro_desktop_parameters_menu'));
    }

    /**
     * @Route("/regenerate_rss", name="icap_notification_regenerate_rss_url")
     * @Template("IcapNotificationBundle:Parameters:config.html.twig")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function regenerateRssUrlAction(User $user)
    {
        $parametersManager = $this->getParametersManager();
        $parameters = $parametersManager->regenerateRssId($user->getId());
        $types = $parametersManager->allTypesList($parameters);

        return ['types' => $types, 'rssId' => $parameters->getRssId()];
    }

    /**
     * @return \Icap\NotificationBundle\Manager\NotificationUserParametersManager
     */
    private function getParametersManager()
    {
        return $this->get('icap.notification.manager.notification_user_parameters');
    }
}
