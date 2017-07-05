<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\BuildBreadcrumbEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.breadcrumb_manager")
 */
class BreadcrumbManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"  = @DI\Inject("service_container"),
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     *
     * @param ObjectManager      $om
     * @param ContainerInterface $container
     */
    public function __construct(ObjectManager $om, ContainerInterface $container, StrictDispatcher $dispatcher)
    {
        $this->om = $om;
        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    public function getBreadcrumb($object)
    {
        $class = ClassUtils::getRealClass(get_class($object));
        $eventName = strtolower('breadcrumb_'.str_replace('\\', '_', $class));

        $event = $this->dispatcher->dispatch(
            $eventName,
            'BuildBreadcrumb',
            [$object]
        );

        return $event->getBreadcrumb();
    }

    /**
     * @DI\Observe("breadcrumb_claroline_corebundle_entity_resource_resourcenode")
     */
    public function pathNode(BuildBreadcrumbEvent $event)
    {
        $crumbs = [];
        $node = $event->getObject();
        $parents = $this->container->get('claroline.manager.resource_manager')->getAncestors($node);
        //this might not be the proper way to do these things but we need to find the workspace we want to open the resource.
        //could be stored in session too but it's not that a good idea either

        $workspace = $node->getWorkspace();
        $router = $this->container->get('router');

        $baseManagerRoute = $workspace ?
          $router->generate('claro_workspace_open_tool', ['workspaceId' => $workspace->getId(), 'toolName' => 'resource_manager']) :
          $router->generate('claro_desktop_open_tool', ['toolName' => 'resource_manager']);

        foreach ($parents as $parent) {
            $crumbs[$parent['name']] = $baseManagerRoute."#resources/{$parent['id']}";
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('resource_manager');

        $object = $workspace ?
          $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')->findOneBy(['workspace' => $workspace, 'tool' => $tool]) :
          $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')->findOneBy(['user' => $user, 'tool' => $tool]);

        $object ? $event->setBreadcrumb(array_merge($this->getBreadcrumb($object), $crumbs)) : $event->setBreadcrumb($crumbs);
    }

    /**
     * @DI\Observe("breadcrumb_claroline_corebundle_entity_tool_orderedtool")
     */
    public function pathTool(BuildBreadcrumbEvent $event)
    {
        $orderedTool = $event->getObject();
        $router = $this->container->get('router');
        $translator = $this->container->get('translator');

        $workspace = $orderedTool->getWorkspace();

        if ($workspace) {
            $breadcrumb = [
            $workspace->getName() => $router->generate('claro_workspace_open', ['workspaceId' => $workspace->getId()]),
            $translator->trans('resource_manager', [], 'tools') => $router->generate('claro_workspace_open_tool', ['workspaceId' => $workspace->getId(), 'toolName' => 'resource_manager']),
          ];
        } else {
            $breadcrumb = [
            'desktop' => $router->generate('claro_desktop_open'),
            $translator->trans('resource_manager', [], 'tools') => $router->generate('claro_desktop_open_tool', ['toolName' => 'resource_manager']),
          ];
        }

        $event->setBreadcrumb($breadcrumb);
    }
}
