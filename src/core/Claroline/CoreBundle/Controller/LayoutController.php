<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

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
    public function topBarAction($workspaceId = null)
    {
        $isLogged = false;
        $countUnreadMessages = 0;
        $username = null;
        $registerTarget = null;
        $loginTarget = null;
        $workspaces = null;
        $personalWs = null;
        $currentWs = null;
        $isInAWorkspace = false;
        $tags = null;
        $tagsWorkspaces = array();

        $token = $this->get('security.context')->getToken();
        $user = $token->getUser();
        $roles = $this->get('claroline.security.utilities')->getRoles($token);
        $em = $this->get('doctrine.orm.entity_manager');
        $wsRepo = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');

        if (!is_null($workspaceId)) {
            $currentWs = $wsRepo->findOneById($workspaceId);

            if (!empty($currentWs)) {
                $isInAWorkspace = true;
            }
        }

        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $isLogged = true;
        }

        if ($isLogged) {
            $isLogged = true;
            $countUnreadMessages = $em->getRepository('ClarolineCoreBundle:Message')
                ->countUnread($user);
            $username = $user->getFirstName() . ' ' . $user->getLastName();
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->findWorkspacesFromLogs();
        } else {
            $username = $this->get('translator')->trans('anonymous', array(), 'platform');
            $workspaces = $wsRepo->findByAnonymous();
            $configHandler = $this->get('claroline.config.platform_config_handler');

            if (true === $configHandler->getParameter('allow_self_registration')) {
                $registerTarget = 'claro_registration_user_registration_form';
            }

            $loginTarget = $this->get('router')->generate('claro_desktop_open');

            if (count($workspaces) > 0) {
                $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                    ->findNonEmptyAdminTagsByWorspaces($workspaces);
                $relTagsWorkspaces = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
                    ->findByAdminAndWorkspaces($workspaces);

                foreach ($relTagsWorkspaces as $tagsWs) {

                    if (empty($tagsWorkspaces[$tagsWs['tag_id']])) {
                        $tagsWorkspaces[$tagsWs['tag_id']] = array();
                    }
                    $tagsWorkspaces[$tagsWs['tag_id']][] = $tagsWs['rel_ws_tag'];
                }
            }
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
                "isImpersonated" => $this->isImpersonated(),
                'isInAWorkspace' => $isInAWorkspace,
                'currentWorkspace' => $currentWs,
                'tags' => $tags,
                'tagsWorkspaces' => $tagsWorkspaces
            )
        );
    }

    /**
     * Renders the warning bar when a workspace role is impersonated.
     *
     * @return Response
     */
    public function renderWarningImpersonationAction()
    {
        $token = $this->get('security.context')->getToken();
        $roles = $this->get('claroline.security.utilities')->getRoles($token);
        $em = $this->get('doctrine.orm.entity_manager');
        $impersonatedRole = null;

        foreach ($roles as $role) {
            if (strstr($role, 'ROLE_WS')) {
                $impersonatedRole = $role;
            }
        }

        if ($impersonatedRole === null) {
            $roleName = 'ROLE_ANONYMOUS';
        } else {
            $workspaceId = substr($impersonatedRole, strripos($impersonatedRole, '_') + 1);
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                ->find($workspaceId);
            $roleEntity = $em->getRepository('ClarolineCoreBundle:Role')
                ->findOneByName($impersonatedRole);
            $roleName = $roleEntity->getTranslationKey();
        }

        return $this->render(
            'ClarolineCoreBundle:Layout:impersonation_alert.html.twig',
            array('workspace' => $workspace->getName(), 'role' => $roleName)
        );
    }

    private function isImpersonated()
    {
        foreach ($this->container->get('security.context')->getToken()->getRoles() as $role) {
            if ($role instanceof \Symfony\Component\Security\Core\Role\SwitchUserRole) {
                return true;
            }
        }

        return false;
    }

    private function findWorkspacesFromLogs()
    {
        $token = $this->get('security.context')->getToken();
        $user = $token->getUser();
        $roles = $this->get('claroline.security.utilities')->getRoles($token);
        $em = $this->get('doctrine.orm.entity_manager');
        $wsLogs = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findLatestWorkspaceByUser($user, $roles);
        $workspaces = array();

        if (!empty($wsLogs)) {
            foreach ($wsLogs as $wsLog) {
                $workspaces[] = $wsLog['workspace'];
            }
        }

        return $workspaces;
    }
}