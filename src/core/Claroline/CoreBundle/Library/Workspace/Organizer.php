<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workspace.organizer")
 */
class Organizer
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __contruct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getDatasForWorkspaceList($withRoles = true)
    {
        $workspaces = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findNonPersonal();
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findNonEmptyAdminTags();
        $relTagWorkspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByAdmin();
        $tagWorkspaces = array();

        // create an array: tagId => [associated_workspace_relation]
        foreach ($relTagWorkspace as $tagWs) {

            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = array();
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }

        $tagsHierarchy = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
            ->findAllAdmin();
        $rootTags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findAdminRootTags();
        $hierarchy = array();

        // create an array : tagId => [direct_children_id]
        foreach ($tagsHierarchy as $tagHierarchy) {

            if ($tagHierarchy->getLevel() === 1) {

                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {

                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        // create an array indicating which tag is displayable
        // a tag is displayable if it or one of his children contains is associated to a workspace
        $displayable = array();
        $allAdminTags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);

        foreach ($allAdminTags as $adminTag) {
            $adminTagId = $adminTag->getId();
            $displayable[$adminTagId] = $this->isTagDisplayable($adminTagId, $tagWorkspaces, $hierarchy);
        }

        $workspaceRoles = array();

        if ($withRoles) {
            $roles = $this->em->getRepository('ClarolineCoreBundle:Role')->findAll();

            foreach ($roles as $role) {
                $wsRole = $role->getWorkspace();

                if (!is_null($wsRole)) {
                    $code = $wsRole->getCode();

                    if (!isset($workspaceRoles[$code])) {
                        $workspaceRoles[$code] = array();
                    }

                    $workspaceRoles[$code][] = $role;
                }
            }
        }

        $datas = array();
        $datas['workspaces'] = $workspaces;
        $datas['tags'] = $tags;
        $datas['tagWorkspaces'] = $tagWorkspaces;
        $datas['hierarchy'] = $hierarchy;
        $datas['rootTags'] = $rootTags;
        $datas['displayable'] = $displayable;
        $datas['workspaceRoles'] = $workspaceRoles;

        return $datas;
    }


    /**
     * Checks if given tag or at least one of its children is associated to a workspace
     *
     * @param integer $tagId
     * @param array $tagWorkspaces
     * @param array $hierarchy
     * @return boolean
     */
    private function isTagDisplayable($tagId, array $tagWorkspaces, array $hierarchy)
    {
        $displayable = false;

        if (isset($tagWorkspaces[$tagId]) && count($tagWorkspaces[$tagId]) > 0) {
            $displayable = true;
        } else {

            if (isset($hierarchy[$tagId]) && count($hierarchy[$tagId]) > 0) {
                $children = $hierarchy[$tagId];

                foreach ($children as $child) {

                    $displayable = $this->isTagDisplayable($child->getId(), $tagWorkspaces, $hierarchy);

                    if ($displayable) {
                        break;
                    }
                }
            }
        }

        return $displayable;
    }
}