<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service()
 */
class ResourceListener
{
    /** @var ResourceNodeManager */
    private $resourceNodeManager;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var ResourceLifecycleManager */
    private $resourceLifecycleManager;

    /** @var Crud */
    private $crud;

    /**
     * ResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "resourceNodeManager"      = @DI\Inject("claroline.manager.resource_node"),
     *     "resourceManager"          = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceLifecycleManager" = @DI\Inject("claroline.manager.resource_lifecycle"),
     *     "resourceRightsManager"    = @DI\Inject("claroline.manager.rights_manager"),
     *     "crud"                     = @DI\Inject("claroline.api.crud")
     * })
     *
     * @param ResourceNodeManager $resourceNodeManager
     * @param ResourceManager     $resourceManager
     */
    public function __construct(
        ResourceNodeManager $resourceNodeManager,
        ResourceManager $resourceManager,
        ResourceLifecycleManager $resourceLifecycleManager,
        RightsManager $resourceRightsManager,
        Crud $crud
    ) {
        $this->resourceNodeManager = $resourceNodeManager;
        $this->resourceManager = $resourceManager;
        $this->resourceLifecycleManager = $resourceLifecycleManager;
        $this->crud = $crud;
        $this->resourceRightsManager = $resourceRightsManager;
    }

    /**
     * @DI\Observe("resource.rights")
     *
     * @param ResourceActionEvent $event
     */
    public function onRights(ResourceActionEvent $event)
    {
        // forward to the resource type
        $data = $event->getData();
        $this->crud->update(ResourceNode::class, $data);
        $this->resourceLifecycleManager->rights($event->getResourceNode(), $event->getData());

        $event->setResponse(new JsonResponse($data));
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
        return $this->resourceLifecycleManager->open($event->getResourceNode());
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
        $this->crud->update(ResourceNode::class, $data);
        $event->setResponse(new JsonResponse($data));
        $event->stopPropagation();
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

            if (0 === count($toUnlock)) {
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
