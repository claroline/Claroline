<?php

namespace Claroline\CoreBundle\Listener\Exception;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service()
 */
class OpenResourceListener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @DI\Observe("kernel.exception")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException()->getPrevious();
        $toUnlock = [];

        if ($exception && get_class($exception) === 'Claroline\CoreBundle\Exception\ResourceAccessException') {
            foreach ($exception->getNodes() as $node) {
                $unlock = $this->container->get('claroline.manager.resource_node')->requiresUnlock($node);

                if ($unlock) {
                    $toUnlock[] = $node;
                }
            }

            //currently, only support one resource unlocking
            $node = $toUnlock[0];

            $content = $this->container->get('templating')->render(
                      'ClarolineCoreBundle:Resource:unlockCodeFormWithLayout.html.twig',
                      ['node' => $node, '_resource' => $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($node)]
                    );

            $event->setResponse(new Response($content));
        }
    }
}
