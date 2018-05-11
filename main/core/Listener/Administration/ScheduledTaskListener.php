<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scheduled tasks tool.
 *
 * @DI\Service()
 */
class ScheduledTaskListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /**
     * ScheduledTaskListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"    = @DI\Inject("templating"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param TwigEngine                   $templating
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(
        TwigEngine $templating,
        PlatformConfigurationHandler $configHandler)
    {
        $this->templating = $templating;
        $this->configHandler = $configHandler;
    }

    /**
     * Displays scheduled tasks administration tool.
     *
     * @DI\Observe("administration_tool_tasks_scheduling")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:scheduled_task\index.html.twig', [
                'isCronConfigured' => $this->configHandler->hasParameter('is_cron_configured') && $this->configHandler->getParameter('is_cron_configured'),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
