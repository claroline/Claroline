<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service()
 */
class ResourceListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var ResourceNodeManager */
    private $resourceNodeManager;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var ResourceLifecycleManager */
    private $resourceLifecycleManager;

    /**
     * ResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"          = @DI\Inject("templating"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param TwigEngine          $templating
     * @param ResourceNodeManager $resourceNodeManager
     * @param ResourceManager     $resourceManager
     */
    public function __construct(
        TwigEngine $templating,
        ResourceNodeManager $resourceNodeManager,
        ResourceManager $resourceManager)
    {
        $this->templating = $templating;
        $this->resourceNodeManager = $resourceNodeManager;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @DI\Observe("resource.create")
     *
     * @param ResourceActionEvent $event
     */
    public function onCreate(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->resourceLifecycleManager->create($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.open")
     *
     * @param ResourceActionEvent $event
     */
    public function onOpen(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->resourceLifecycleManager->open($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.about")
     *
     * @param ResourceActionEvent $event
     */
    public function onAbout(ResourceActionEvent $event)
    {
        // todo return the full serialized version of the resource node
    }

    /**
     * @DI\Observe("resource.configure")
     *
     * @param ResourceActionEvent $event
     */
    public function onConfigure(ResourceActionEvent $event)
    {
        $data = $event->getData();

        // todo deserialize data into the node
    }

    /**
     * @DI\Observe("resource.edit")
     *
     * @param ResourceActionEvent $event
     */
    public function onEdit(ResourceActionEvent $event)
    {
        $this->resourceLifecycleManager->edit($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.export")
     *
     * @param ResourceActionEvent $event
     */
    public function onExport(ResourceActionEvent $event)
    {
        $this->resourceLifecycleManager->export($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.delete")
     *
     * @param ResourceActionEvent $event
     */
    public function onDelete(ResourceActionEvent $event)
    {
        $this->resourceLifecycleManager->delete($event->getResourceNode());
    }

    /**
     * Handles resources access errors due to restrictions configuration.
     *
     * @DI\Observe("kernel.exception")
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @todo : find another way to manage (maybe in the on open / load event)
     */
    public function handleAccessRestrictions(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException()->getPrevious();
        if ($exception && $exception instanceof ResourceAccessException) {
            $toUnlock = [];
            foreach ($exception->getNodes() as $node) {
                $unlock = $this->resourceNodeManager->requiresUnlock($node);

                if ($unlock) {
                    $toUnlock[] = $node;
                }
            }

            if (count($toUnlock) === 0) {
                return;
            }

            // currently, only support one resource unlocking
            $node = $toUnlock[0];

            $content = $this->templating->render(
              'ClarolineCoreBundle:Resource:unlockCodeFormWithLayout.html.twig', [
                  'node' => $node,
                  '_resource' => $this->resourceManager->getResourceFromNode($node),
              ]);

            $event->setResponse(new Response($content));
        }
    }
}
