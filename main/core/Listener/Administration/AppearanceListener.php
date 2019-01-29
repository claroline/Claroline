<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class AppearanceListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /** @var ParametersSerializer */
    private $serializer;

    /** @var ThemeManager */
    private $themeManager;

    /** @var IconSetManager */
    private $iconSetManager;

    /**
     * AppearanceListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "finder"         = @DI\Inject("claroline.api.finder"),
     *     "serializer"     = @DI\Inject("claroline.serializer.parameters"),
     *     "themeManager"   = @DI\Inject("claroline.manager.theme_manager"),
     *     "iconSetManager" = @DI\Inject("claroline.manager.icon_set_manager")
     * })
     *
     * @param TwigEngine           $templating
     * @param FinderProvider       $finder
     * @param ParametersSerializer $serializer
     * @param ThemeManager         $themeManager
     * @param IconSetManager       $iconSetManager
     */
    public function __construct(
        TwigEngine $templating,
        FinderProvider $finder,
        ParametersSerializer $serializer,
        ThemeManager $themeManager,
        IconSetManager $iconSetManager
    ) {
        $this->templating = $templating;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->themeManager = $themeManager;
        $this->iconSetManager = $iconSetManager;
    }

    /**
     * Displays appearance administration tool.
     *
     * @DI\Observe("administration_tool_appearance_settings")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $iconSets = $this->iconSetManager->listIconSetsByType(IconSetTypeEnum::RESOURCE_ICON_SET);

        // TODO : do it front side
        $iconSetChoices = [];
        foreach ($iconSets as $set) {
            $iconSetChoices[$set->getName()] = $set->getName();
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:appearance.html.twig', [
                'context' => [
                    'type' => Tool::ADMINISTRATION,
                ],
                'parameters' => $this->serializer->serialize(),
                'iconSetChoices' => $iconSetChoices,
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
