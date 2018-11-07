<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\PortalManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class ParametersListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ParametersSerializer */
    private $serializer;

    /** @var LocaleManager */
    private $localeManager;

    /** @var PortalManager */
    private $portalManager;

    /**
     * AppearanceListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"    = @DI\Inject("templating"),
     *     "translator"    = @DI\Inject("translator"),
     *     "serializer"    = @DI\Inject("claroline.serializer.parameters"),
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager"),
     *     "portalManager" = @DI\Inject("claroline.manager.portal_manager")
     * })
     *
     * @param TwigEngine           $templating
     * @param TranslatorInterface  $translator
     * @param ParametersSerializer $serializer
     * @param LocaleManager        $localeManager
     * @param PortalManager        $portalManager
     */
    public function __construct(
        TwigEngine $templating,
        TranslatorInterface $translator,
        ParametersSerializer $serializer,
        LocaleManager $localeManager,
        PortalManager $portalManager
    ) {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->serializer = $serializer;
        $this->localeManager = $localeManager;
        $this->portalManager = $portalManager;
    }

    /**
     * Displays parameters administration tool.
     *
     * @DI\Observe("administration_tool_main_settings")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $portalResources = $this->portalManager->getPortalEnabledResourceTypes();

        // todo : do it front side
        $portalChoices = [];
        foreach ($portalResources as $portalResource) {
            $portalChoices[$portalResource] = $this->translator->trans($portalResource, [], 'resource');
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:parameters.html.twig', [
                'context' => [
                    'type' => Tool::ADMINISTRATION,
                ],
                'parameters' => $this->serializer->serialize(),
                'availableLocales' => array_keys($this->localeManager->getImplementedLocales()),
                'portalResources' => $portalChoices,
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
