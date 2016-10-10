<?php

namespace Icap\BadgeBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\BadgeBundle\Manager\BadgeManager;
use Icap\BadgeBundle\Manager\BadgeWidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var string
     */
    private $platformName;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "templating"         = @DI\Inject("templating"),
     *     "badgeUsageForm"     = @DI\Inject("icap_badge.widget.form.badge_usage"),
     *     "badgeManager"       = @DI\Inject("icap_badge.manager.badge"),
     *     "badgeClaimManager"  = @DI\Inject("icap_badge.manager.badge_claim"),
     *     "badgeWidgetManager" = @DI\Inject("icap_badge.manager.badge_widget"),
     *     "configHandler"      = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        TwigEngine $templating,
        FormInterface $badgeUsageForm,
        BadgeManager $badgeManager,
        BadgeWidgetManager $badgeWidgetManager,
        PlatformConfigurationHandler $configHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->templating = $templating;
        $this->badgeUsageForm = $badgeUsageForm;
        $this->badgeManager = $badgeManager;
        $this->badgeWidgetManager = $badgeWidgetManager;
        $this->platformName = $configHandler->getParameter('name');

        if ($this->platformName === null || empty($this->platformName)) {
            $this->platformName = 'Claroline';
        }
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("widget_badge_usage")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();
        $user = $this->tokenStorage->getToken()->getUser();
        $badgeWidgetConfig = $this->badgeWidgetManager->getBadgeUsageConfigForInstance($widgetInstance);
        $simple_view_widget = $badgeWidgetConfig->isSimpleView();
        $nbLastAwarded = $badgeWidgetConfig->getNumberLastAwardedBadge();
        $nbMostAwarded = $badgeWidgetConfig->getNumberMostAwardedBadge();
        $mostAwardedBadges = [];
        $availableBadges = [];

        if (!is_null($workspace)) {
            $lastAwardedBadges = $user !== 'anon.' ?
                $this->badgeManager->getWorkspaceLastAwardedBadgesToLoggedUser($workspace, $nbLastAwarded) :
                [];
            $mostAwardedBadges = $this->badgeManager->getWorkspaceMostAwardedBadges($workspace, $nbMostAwarded);
            $availableBadges = $this->badgeManager->getWorkspaceAvailableBadges($workspace);
            $widgetType = 'workspace';
        } else {
            $lastAwardedBadges = $this->badgeManager->getLoggedUserLastAwardedBadges($nbLastAwarded);
            $widgetType = 'desktop';
        }
        $content = $this->templating->render(
            'IcapBadgeBundle:Widget:badge_usage.html.twig',
            [
                'lastAwardedBadges' => $lastAwardedBadges,
                'mostAwardedBadges' => $mostAwardedBadges,
                'availableBadges' => $availableBadges,
                'simple_view_widget' => $simple_view_widget,
                'systemName' => $this->platformName,
                'widgetType' => $widgetType,
            ]
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
            [
                'form' => $this->badgeUsageForm->createView(),
                'instance' => $event->getInstance(),
            ]
        );
        $event->setContent($content);
    }
}
