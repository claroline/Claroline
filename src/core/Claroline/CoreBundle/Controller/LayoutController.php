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
     * (anonymous/logged, profile, etc.) and the platform options (e.g. self-
     * registration allowed/prohibited).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction()
    {
        $isLogged = false;
        $countUnreadMessages = 0;
        $username = null;
        $registerTarget = null;
        $loginTarget = null;
        $workspaces = null;
        $personalWs = null;

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $wsRepo = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');

        if ($user instanceof User) {
            $isLogged = true;
            $countUnreadMessages = $em->getRepository('ClarolineCoreBundle:Message')
                ->countUnread($user);
            $username = $user->getFirstName() . ' ' . $user->getLastName();
            $workspaces = $wsRepo->findByUser($user);
            $personalWs = $user->getPersonalWorkspace();
        } else {
            $username = $this->get('translator')->trans('anonymous', array(), 'platform');
            $workspaces = $wsRepo->findByAnonymous();
            $configHandler = $this->get('claroline.config.platform_config_handler');

            if (true === $configHandler->getParameter('allow_self_registration')) {
                $registerTarget = 'claro_registration_user_registration_form';
            }

            $loginTarget = $this->get('router')->generate('claro_desktop_open');
        }

        return $this->render(
            'ClarolineCoreBundle:Layout:top_bar.html.twig',
            array(
                'isLogged' => $isLogged,
                'countUnreadMessages' => $countUnreadMessages,
                'username' => $username,
                'register_target' => $registerTarget,
                'login_target' => $loginTarget,
                'workspaces' => $workspaces,
                'personalWs' => $personalWs,
            )
        );
    }
}