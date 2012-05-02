<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;

/**
 * Note : Actions of this controller are not routed. They're intended to be
 * rendered directly in the "common_layout" and "content_layout" templates.
 */
class LayoutController extends Controller
{
    public function headerAction()
    {
        return $this->render('ClarolineCoreBundle:Layout:header.html.twig');
    }

    public function footerAction()
    {
        return $this->render('ClarolineCoreBundle:Layout:footer.html.twig');
    }

    public function topBarAction()
    {
        $connected = false;
        $username = null;
        $registerTarget = null;
        $loginTarget = null;
        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user instanceof User)
        {
            $connected = true;
            $username = $user->getUsername();
        }
        else
        {
            $configHandler = $this->get('claroline.config.platform_config_handler');
            
            if (true === $configHandler->getParameter('allow_self_registration'))
            {
                $registerTarget = 'claro_user_registration_form';
            }
            
            // TODO : use a configuration option (-> admin)
            $loginTarget = 'claro_desktop_index';
        }

        return $this->render(
            'ClarolineCoreBundle:Layout:top_bar.html.twig', 
            array(
                'connected' => $connected,
                'username' => $username,
                'register_target' => $registerTarget,
                'login_target' => $loginTarget
            )
        );
    }
}