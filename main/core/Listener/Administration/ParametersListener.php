<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class ParametersListener
{
    /** @var ParametersSerializer */
    private $serializer;

    /** @var LocaleManager */
    private $localeManager;

    /**
     * ParametersListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer"    = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer"),
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager")
     * })
     *
     * @param ParametersSerializer $serializer
     * @param LocaleManager        $localeManager
     */
    public function __construct(
        ParametersSerializer $serializer,
        LocaleManager $localeManager
    ) {
        $this->serializer = $serializer;
        $this->localeManager = $localeManager;
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
        $event->setData([
            'parameters' => $this->serializer->serialize(),
            'availableLocales' => array_keys($this->localeManager->getImplementedLocales()),
        ]);

        $event->stopPropagation();
    }
}
