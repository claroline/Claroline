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
        $em = $this->getDoctrine()->getEntityManager();
        $launcherRepo = $em->getRepository('Claroline\PluginBundle\Entity\ApplicationLauncher');

        $user = $this->get('security.context')->getToken()->getUser();

        if (! $user instanceof User)
        {
            $launchers = $launcherRepo->findByAccessRoles(array('ROLE_ANONYMOUS'));
        }
        else
        {
            $roles = array();
            
            foreach ($user->getRoles() as $role)
            {
                $roles[] = $role->getName();
            }

            $launchers = $launcherRepo->findByAccessRoles($roles);
        }

        return $this->render('ClarolineGUIBundle:Widget:app_menu.html.twig', array('launchers' => $launchers));
    }
}