<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class HomeListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /**
     * AnalyticsListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "finder"     = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param TwigEngine     $templating
     * @param FinderProvider $finder
     */
    public function __construct(
        TwigEngine $templating,
        FinderProvider $finder
    ) {
        $this->templating = $templating;
        $this->finder = $finder;
    }

    /**
     * Displays analytics administration tool.
     *
     * @DI\Observe("administration_tool_desktop_and_home")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $tabs = $this->finder->search(
          HomeTab::class,
          ['filters' => ['type' => HomeTab::TYPE_ADMIN_DESKTOP]]
        );

        $roles = $this->finder->search('Claroline\CoreBundle\Entity\Role',
          ['filters' => ['type' => Role::PLATFORM_ROLE]]
        );

        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:home.html.twig', [
                'editable' => true,
                'context' => [
                    'type' => Widget::CONTEXT_ADMINISTRATION,
                    'data' => [
                        'roles' => $roles['data'],
                    ],
                ],
                'tabs' => $tabs['data'],
            ]
        );
        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
