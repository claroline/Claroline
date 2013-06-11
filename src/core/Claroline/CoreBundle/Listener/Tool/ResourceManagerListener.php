<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ConfigureWorkspaceToolEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service(scope="request")
 */
class ResourceManagerListener
{
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "templating" = @DI\Inject("templating"),
     *     "manager" = @DI\Inject("claroline.resource.manager"),
     *     "converter" = @DI\Inject("claroline.resource.converter"),
     *     "sc" = @DI\Inject("security.context"),
     *     "request" = @DI\Inject("request")
     * })
     */
    public function __construct($em, $ed, $templating, $manager, $converter, $sc, $request)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->sc = $sc;
        $this->request = $request;
    }

    /**
     * @DI\Observe("open_tool_workspace_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceResouceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceWorkspace($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("configure_workspace_tool_resource_manager")
     *
     * @param ConfigureWorkspaceToolEvent $event
     */
    public function onDisplayWorkspaceResourceConfiguration(ConfigureWorkspaceToolEvent $event)
    {
        $event->setContent($this->workspaceResourceRightsForm($event->getWorkspace()));
    }

    /**
     * @DI\Observe("open_tool_desktop_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopResourceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceDesktop());
    }

    /**
     * Renders the resources page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return string
     */
    public function resourceWorkspace($workspaceId)
    {
        $breadcrumbsIds = $this->request->query->get('_breadcrumbs');
        if ($breadcrumbsIds != null) {
            $ancestors = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findResourcesByIds($breadcrumbsIds);
            if (!$this->manager->isPathValid($ancestors)) {
                throw new \Exception('Breadcrumbs invalid');
            };
        } else {
            $ancestors = array();
        }
        $path = array();

        foreach ($ancestors as $ancestor) {
            $path[] = $this->converter->toArray($ancestor, $this->sc->getToken());
        }

        $jsonPath = json_encode($path);

        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $directoryId = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($workspace)
            ->getId();
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resources.html.twig', array(
                'workspace' => $workspace,
                'directoryId' => $directoryId,
                'resourceTypes' => $resourceTypes,
                'jsonPath' => $jsonPath
             )
        );
    }

    /**
     * Displays the resource manager.
     *
     * @return string
     */
    public function resourceDesktop()
    {
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\resource_manager:resources.html.twig',
            array('resourceTypes' => $resourceTypes)
        );
    }

    private function workspaceResourceRightsForm(AbstractWorkspace $workspace)
    {
        if (!$this->sc->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
        $resource = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $roleRights = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findNonAdminRights($resource);

        $workspaces = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findNonPersonal();
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findNonEmptyAdminTags();
        $relTagWorkspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByAdmin();
        $roles = $this->em->getRepository('ClarolineCoreBundle:Role')->findAll();
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
            $displayable[$adminTagId] = WorkspaceTag::isTagDisplayable($adminTagId, $tagWorkspaces, $hierarchy);
        }

        $workspaceRoles = array();

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

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resources_rights.html.twig',
            array(
                'workspace' => $workspace,
                'resource' => $resource,
                'roleRights' => $roleRights,
                'workspaces' => $workspaces,
                'tags' => $tags,
                'tagWorkspaces' => $tagWorkspaces,
                'hierarchy' => $hierarchy,
                'rootTags' => $rootTags,
                'displayable' => $displayable,
                'workspaceRoles' => $workspaceRoles
            )
        );
    }
}
