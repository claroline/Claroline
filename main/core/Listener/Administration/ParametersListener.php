<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\LocaleManager;

class ParametersListener
{
    /** @var ParametersSerializer */
    private $serializer;

    /** @var LocaleManager */
    private $localeManager;

    /**
     * ParametersListener constructor.
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
