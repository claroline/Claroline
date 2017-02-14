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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities as SecurityUtilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class WorkspaceAccessExtension extends \Twig_Extension
{
    private $wm;
    private $em;
    private $ut;
    private $tokenStorage;
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "wm"            = @DI\Inject("claroline.manager.workspace_manager"),
     *     "em"            = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ut"            = @DI\Inject("claroline.security.utilities"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        WorkspaceManager $wm,
        EntityManager $em,
        AuthorizationCheckerInterface $authorization,
        SecurityUtilities $ut,
        TokenStorageInterface $tokenStorage
    ) {
        $this->wm = $wm;
        $this->em = $em;
        $this->ut = $ut;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'get_workspaces_accesses' => new \Twig_Function_Method($this, 'getAccesses'),
            'has_role_access_to_workspace' => new \Twig_Function_Method($this, 'hasRoleAccess'),
            'has_access_to_workspace' => new \Twig_Function_Method($this, 'hasAccess'),
            'has_role_in_workspace' => new \Twig_Function_Method($this, 'hasRoleInWorkspace'),
            'is_date_access_valid' => new \Twig_Function_Method($this, 'isDateAccessValid'),
        ];
    }

    public function isDateAccessValid($workspace)
    {
        if (!$workspace) {
            return true;
        }

        $isEndValid = $workspace->getEndDate() ? $workspace->getEndDate() > new \DateTime() : true;
        $isStartValid = $workspace->getStartDate() ? $workspace->getStartDate() < new \DateTime() : true;

        return $isEndValid && $isStartValid;
    }

    public function getAccesses(array $workspaces)
    {
        return $this->wm->getAccesses($this->tokenStorage->getToken(), $workspaces);
    }

    public function hasRoleAccess($role, $workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $repo->findDisplayedByRolesAndWorkspace([$role], $workspace);

        return count($tools) > 0 && $this->isDateAccessValid($workspace);
    }

    public function hasAccess($workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->find($workspaceId);

        return $this->authorization->isGranted('OPEN', $workspace);
    }

    public function hasRoleInWorkspace($workspaceId, TokenInterface $token)
    {
        $roles = $this->ut->getRoles($token);
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $workspaceRoles = $repo->findRolesByWorkspaceAndRoleNames($workspace, $roles);

        return count($workspaceRoles) > 0 ? true : false;
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
