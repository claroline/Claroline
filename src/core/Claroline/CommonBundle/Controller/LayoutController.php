<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\UserBundle\Entity\User;

/**
 * Note : Actions of this controller are not routed. They're intended to be
 *        rendered directly in the "common_layout" and "content_layout" templates.
 *        Note that defining this controller as a service is incompatible
 *        with this technique (the twig/symfony "render" statement seems to 
 *        work only with the symfony Controller class).
 */
class LayoutController extends Controller
{
    public function headerAction()
    {
        return $this->render('ClarolineCommonBundle:Layout:header.html.twig');
    }

    public function footerAction()
    {
        return $this->render('ClarolineCommonBundle:Layout:footer.html.twig');
    }

    public function topBarAction()
    {
        $connected = false;
        $username = null;
        $loginTarget = null;
        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user instanceof User)
        {
            $connected = true;
            $username = $user->getFirstName() . ' ' . $user->getLastName();
        }
        else
        {
            // TODO : use a configuration option (-> admin)
            $loginTarget = 'claro_desktop_index';
        }

        return $this->render(
            'ClarolineCommonBundle:Layout:top_bar.html.twig', 
            array(
                'connected' => $connected,
                'username' => $username,
                'login_target' => $loginTarget
            )
        );
    }
}