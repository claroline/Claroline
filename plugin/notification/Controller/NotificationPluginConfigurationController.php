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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class NotificationPluginConfigurationController extends Controller
{
    /**
     * @Route("/configuration", name="icap_notification_configuration")
     * @Template("IcapNotificationBundle:Configuration:config.html.twig")
     * @Method({"GET"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAction(User $user)
    {
        $configManager = $this->getNotificationPluginConfigurationManager();
        $form = $configManager->getForm();

        return array('form' => $form->createView());
    }

    /**
     * @Route("/configuration", name="icap_notification_configuration_save")
     * @Template("IcapNotificationBundle:Configuration:config.html.twig")
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

        return array('form' => $form->createView());
    }

    /**
     * @return \Icap\NotificationBundle\Manager\NotificationPluginConfigurationManager
     */
    private function getNotificationPluginConfigurationManager()
    {
        return $this->get('icap.notification.manager.plugin_configuration');
    }
}
