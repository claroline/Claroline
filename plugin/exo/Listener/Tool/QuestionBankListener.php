<?php

namespace UJM\ExoBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service("ujm_exo.listener.question_bank")
 */
class QuestionBankListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("open_tool_desktop_ujm_questions")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate([], null, [
            '_controller' => 'UJMExoBundle:Tool\QuestionBank:open',
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }
}
