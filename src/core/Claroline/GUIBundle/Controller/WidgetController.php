<?php

namespace Claroline\GUIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\UserBundle\Entity\User;

class WidgetController extends Controller
{
    public function headerAction()
    {
        return $this->render('ClarolineGUIBundle:Widget:header.html.twig');
    }

    public function footerAction()
    {
        return $this->render('ClarolineGUIBundle:Widget:footer.html.twig');
    }

    public function statusBarAction()
    {
        $connected = false;
        $username = null;
        $loginTarget = null;
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user instanceof User)
        {
            $connected = true;
            $username = $user->getFirstName() . ' ' . $user->getLastName();
        }
        else
        {
            // TODO: look into configuration : connection target
            $loginTarget = 'claro_core_desktop';
        }

        return $this->render('ClarolineGUIBundle:Widget:status_bar.html.twig', array(
            'connected' => $connected,
            'username' => $username,
            'login_target' => $loginTarget));
    }

    public function applicationMenuAction()
    {
        /*
        $em = $this->getDoctrine()->getEntityManager();
        $appRepo = $em->getRepository('ClarolineGUIBundle\Entity\Application');

        $user = $this->get('security.context')->getToken()->getUser();

        if (! $user instanceof User)
        {
            $apps = $appRepo->findByRoles(array('ROLE_ANONYMOUS'));
        }
        else
        {
            $roles = array();
            
            foreach ($user->getRoles() as $role)
            {
                $roles[] = $role->getName();
            }

            $apps = $appRepo->findByRoles($roles);
        }*/

        $apps = array();

        return $this->render('ClarolineGUIBundle:Widget:app_menu.html.twig', array('apps' => $apps));
    }
}