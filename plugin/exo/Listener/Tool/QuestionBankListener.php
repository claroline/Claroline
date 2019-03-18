<?php

namespace UJM\ExoBundle\Listener\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @DI\Service("ujm_exo.listener.question_bank")
 */
class QuestionBankListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * ResourcesListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(
        TwigEngine $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("open_tool_desktop_ujm_questions")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $content = $this->templating->render(
            'UJMExoBundle:tool:question_bank.html.twig', [
                'context' => [
                    'type' => Tool::DESKTOP,
                ],
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
