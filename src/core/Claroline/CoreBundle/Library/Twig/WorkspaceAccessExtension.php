<?php

namespace Claroline\CoreBundle\Library\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\Utilities as SecurityUtilities;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class WorkspaceAccessExtension extends \Twig_Extension
{
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ut" = @DI\Inject("claroline.security.utilities"),
     * })
     */
    public function __construct(EntityManager $em, SecurityUtilities $ut)
    {
        $this->em = $em;
        $this->ut = $ut;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'has_role_access_to_workspace' => new \Twig_Function_Method($this, 'hasRoleAccess'),
            'has_access_to_workspace' => new \Twig_Function_Method($this, 'hasAccess'),
        );
    }

    public function hasRoleAccess($role, $workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $repo->findByRolesAndWorkspace(array($role), $workspace, true);

        return count($tools) > 0 ? true: false;
    }

    public function hasAccess($workspaceId, TokenInterface $token)
    {
        $roles = $this->ut->getRoles($token);
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $repo->findByRolesAndWorkspace($roles, $workspace, true);

        return count($tools) > 0 ? true: false;
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'workspace_access';
    }
}
