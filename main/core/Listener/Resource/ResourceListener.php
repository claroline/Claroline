<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Exception\ResourceAccessException;
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
     * Handles resources access errors due to restrictions configuration.
     *
     * @DI\Observe("kernel.exception")
     *
     * @param GetResponseForExceptionEvent $event
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
