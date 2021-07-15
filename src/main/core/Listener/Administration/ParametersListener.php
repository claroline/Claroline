<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Claroline\ThemeBundle\Manager\IconSetManager;

class ParametersListener
{
    /** @var ObjectManager */
    private $om;

    /** @var ParametersSerializer */
    private $serializer;

    /** @var LocaleManager */
    private $localeManager;

    /** @var IconSetManager */
    private $iconSetManager;

    public function __construct(
        ObjectManager $om,
        ParametersSerializer $serializer,
        LocaleManager $localeManager,
        IconSetManager $iconSetManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->localeManager = $localeManager;
        $this->iconSetManager = $iconSetManager;
    }

    /**
     * Displays parameters administration tool.
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $iconSets = $this->iconSetManager->listIconSetsByType(IconSet::RESOURCE_ICON_SET);
        $parameters = $this->serializer->serialize();

        $event->setData([
            'lockedParameters' => $parameters['lockedParameters'] ?? [],
            'parameters' => $parameters,
            'availableLocales' => $this->localeManager->getAvailableLocales(),
            'iconSetChoices' => array_map(function (IconSet $iconSet) {
                return $iconSet->getName(); // TODO : create a serializer
            }, $iconSets),
            'mimeTypes' => $this->iconSetManager->fetchAllResourcesMimeTypes(),
        ]);

        $event->stopPropagation();
    }
}
