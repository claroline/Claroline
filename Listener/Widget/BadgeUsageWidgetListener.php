<?php

namespace Icap\BadgeBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Icap\BadgeBundle\Manager\BadgeManager;
use Icap\BadgeBundle\Manager\BadgeWidgetManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormInterface;
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
     * @var \Symfony\Component\Form\FormInterface
     */
    private $badgeUsageForm;

    /**
     * @var \Icap\BadgeBundle\Manager\BadgeManager
     */
    private $badgeManager;

    /**
     * @var \Icap\BadgeBundle\Manager\BadgeWidgetManager
     */
    private $badgeWidgetManager;

    /**
     * @DI\InjectParams({
     *     "templating"         = @DI\Inject("templating"),
     *     "badgeUsageForm"     = @DI\Inject("icap_badge.widget.form.badge_usage"),
     *     "badgeManager"       = @DI\Inject("icap_badge.manager.badge"),
     *     "badgeWidgetManager" = @DI\Inject("icap_badge.manager.badge_widget")
     * })
     */
    public function __construct(TwigEngine $templating, FormInterface $badgeUsageForm, BadgeManager $badgeManager, BadgeWidgetManager $badgeWidgetManager)
    {
        $this->templating         = $templating;
        $this->badgeUsageForm     = $badgeUsageForm;
        $this->badgeManager       = $badgeManager;
        $this->badgeWidgetManager = $badgeWidgetManager;
    }

    /**
     * @DI\Observe("widget_badge_usage")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance    = $event->getInstance();
        $badgeWidgetConfig = $this->badgeWidgetManager->getBadgeUsageConfigForInstance($widgetInstance);
        $lastAwardedBadges = $this->badgeManager->getWorkspaceLastAwardedBadges($widgetInstance->getWorkspace(), $badgeWidgetConfig->getNumberLastAwardedBadge());
        $mostAwardedBadges = $this->badgeManager->getWorkspaceMostAwardedBadges($widgetInstance->getWorkspace(), $badgeWidgetConfig->getNumberMostAwardedBadge());

        $content = $this->templating->render(
            'IcapBadgeBundle:Widget:badge_usage.html.twig',
            array(
                'lastAwardedBadges' => $lastAwardedBadges,
                'mostAwardedBadges' => $mostAwardedBadges
            )
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
        $badgeWidgetConfig = $this->badgeWidgetManager->getBadgeUsageConfigForInstance($event->getInstance());
        $this->badgeUsageForm->setData($badgeWidgetConfig);

        $content = $this->templating->render(
            'IcapBadgeBundle:Widget:badge_usage_config.html.twig',
            array(
                'form'     => $this->badgeUsageForm->createView(),
                'instance' => $event->getInstance()
            )
        );
        $event->setContent($content);
    }
}
