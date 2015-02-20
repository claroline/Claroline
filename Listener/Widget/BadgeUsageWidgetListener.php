<?php

namespace Icap\BadgeBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Manager\BadgeManager;
use Claroline\CoreBundle\Manager\BadgeWidgetManager;
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
     * @var \Symfony\Component\Form\FormInterface
     */
    private $badgeUsageForm;

    /**
     * @var \Claroline\CoreBundle\Manager\BadgeManager
     */
    private $badgeManager;

    private $badgeWidgetManager;

    /**
     * @DI\InjectParams({
     *     "templating"         = @DI\Inject("templating"),
     *     "badgeUsageForm"     = @DI\Inject("claroline.widget.form.badge_usage"),
     *     "badgeManager"       = @DI\Inject("claroline.manager.badge"),
     *     "badgeWidgetManager" = @DI\Inject("claroline.manager.badge_widget")
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
            'ClarolineCoreBundle:Widget:Badge\badge_usage.html.twig',
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
            'ClarolineCoreBundle:Widget:Badge\badge_usage_config.html.twig',
            array(
                'form'     => $this->badgeUsageForm->createView(),
                'instance' => $event->getInstance()
            )
        );
        $event->setContent($content);
    }
}
