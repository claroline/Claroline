<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/14/15
 */

namespace Icap\NotificationBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Exception\InvalidNotificationFormException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NotificationPluginConfigurationController extends Controller
{
    /**
     * @Route("/configuration", name="icap_notification_configuration")
     * @Template("IcapNotificationBundle:configuration:config.html.twig")
     * @Method({"GET"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAction(User $user)
    {
        return [];
    }

    /**
     * @Route("/configuration_old", name="icap_notification_configuration_old")
     * @Template("IcapNotificationBundle:configuration:config.html.twig")
     * @Method({"GET"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getOldAction(User $user)
    {
        $configManager = $this->getNotificationPluginConfigurationManager();
        $form = $configManager->getForm();

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/configuration", name="icap_notification_configuration_save")
     * @Template("IcapNotificationBundle:configuration:config.html.twig")
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $request, User $user)
    {
        $configManager = $this->getNotificationPluginConfigurationManager();
        try {
            $form = $configManager->processForm($request);
            $this->addFlash('success', 'successfully_saved_configuration');
        } catch (InvalidNotificationFormException $infe) {
            $form = $infe->getForm();
            $this->addFlash('error', $infe->getMessage());
        }

        return ['form' => $form->createView()];
    }

    /**
     * @return \Icap\NotificationBundle\Manager\NotificationPluginConfigurationManager
     */
    private function getNotificationPluginConfigurationManager()
    {
        return $this->get('icap.notification.manager.plugin_configuration');
    }
}
