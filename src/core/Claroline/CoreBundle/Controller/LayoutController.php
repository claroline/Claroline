<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;

/**
 * Actions of this controller are not routed. They're intended to be rendered
 * directly in the base "ClarolineCoreBundle::layout.html.twig" template.
 */
class LayoutController extends Controller
{
    /**
     * Displays the platform header.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function headerAction()
    {
        return $this->render('ClarolineCoreBundle:Layout:header.html.twig');
    }

    /**
     * Displays the platform footer.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function footerAction()
    {
        return $this->render('ClarolineCoreBundle:Layout:footer.html.twig');
    }

    /**
     * Displays the platform top bar. Its content depends on the user status
     * (anonymous/connected, profile, etc.) and the platform options (e.g. self-
     * registration allowed/prohibited).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction()
    {
        $connected = false;
        $username = null;
        $registerTarget = null;
        $loginTarget = null;
        $workspaces = null;
        $personalWs = null;
        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user instanceof User) {
            $connected = true;
            $username = $user->getFirstName() . ' ' . $user->getLastName();
            $workspaces = $this->get('doctrine.orm.entity_manager')
                ->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
                ->getAllWsOfUser($user);
            $personalWs = $user->getPersonalWorkspace();

        } else {
            $configHandler = $this->get('claroline.config.platform_config_handler');

            if (true === $configHandler->getParameter('allow_self_registration')) {
                $registerTarget = 'claro_registration_user_registration_form';
            }

            // TODO : use a platform option to make this target configurable
            $loginTarget = 'claro_desktop_index';
        }


        return $this->render(
            'ClarolineCoreBundle:Layout:top_bar.html.twig',
            array(
                'connected' => $connected,
                'username' => $username,
                'register_target' => $registerTarget,
                'login_target' => $loginTarget,
                'workspaces' => $workspaces,
                'personalWs' => $personalWs
            )
        );
    }
}