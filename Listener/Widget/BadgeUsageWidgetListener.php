<?php

namespace Claroline\CoreBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class BadgeUsageWidgetListener
{
    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templating;

    /**
     * @var FormInterface
     */
    private $badgeUsageForm;

    /**
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "badgeUsageForm" = @DI\Inject("claroline.widget.form.badge_usage")
     * })
     */
    public function __construct(TwigEngine $templating, FormInterface $badgeUsageForm)
    {
        $this->templating     = $templating;
        $this->badgeUsageForm = $badgeUsageForm;
    }

    /**
     * @DI\Observe("widget_badge_usage")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:Badge\badge_usage.html.twig',
            array()
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_badge_usage_configuration")
     *
     * @param ConfigureWidgetEvent $event
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:Badge\badge_usage_config.html.twig',
            array(
                'form'     => $this->badgeUsageForm->createView(),
                'instance' => $event->getInstance()
            )
        );
        $event->setContent($content);
    }
}
