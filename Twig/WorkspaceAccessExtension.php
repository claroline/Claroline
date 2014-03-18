<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\Utilities as SecurityUtilities;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;

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
     *     "sc" = @DI\Inject("security.context")
     * })
     */
    public function __construct(EntityManager $em, SecurityUtilities $ut, SecurityContext $sc)
    {
        $this->em = $em;
        $this->ut = $ut;
        $this->sc = $sc;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'has_role_access_to_workspace' => new \Twig_Function_Method($this, 'hasRoleAccess'),
            'has_access_to_workspace' => new \Twig_Function_Method($this, 'hasAccess'),
            'has_role_in_workspace' => new \Twig_Function_Method($this, 'hasRoleInWorkspace')
        );
    }

    public function hasRoleAccess($role, $workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $repo->findDisplayedByRolesAndWorkspace(array($role), $workspace);

        return count($tools) > 0 ? true: false;
    }

    public function hasAccess($workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);

        return $this->sc->isGranted('OPEN', $workspace);
    }

    public function hasRoleInWorkspace($workspaceId, TokenInterface $token)
    {
        $roles = $this->ut->getRoles($token);
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $workspaceRoles = $repo->findRolesByWorkspaceAndRoleNames($workspace, $roles);

        return count($workspaceRoles) > 0 ? true: false;
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
