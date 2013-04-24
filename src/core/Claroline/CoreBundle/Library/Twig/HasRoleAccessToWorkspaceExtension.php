<?php

namespace Claroline\CoreBundle\Library\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class HasRoleAccessToWorkspaceExtension extends \Twig_Extension
{
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'has_role_access_to_workspace' => new \Twig_Function_Method($this, 'hasAccess'),
        );
    }

    public function hasAccess($role, $workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $repo->findByRolesAndWorkspace(array($role), $workspace, true);

        return count($tools) > 0 ? true: false;
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'has_role_access_to_workspace';
    }
}
