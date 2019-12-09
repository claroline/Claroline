<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\LocaleManager;

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

    /**
     * ParametersListener constructor.
     *
     * @param ObjectManager        $om
     * @param ParametersSerializer $serializer
     * @param LocaleManager        $localeManager
     * @param IconSetManager       $iconSetManager
     */
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
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $iconSets = $this->iconSetManager->listIconSetsByType(IconSetTypeEnum::RESOURCE_ICON_SET);
        $parameters = $this->serializer->serialize();

        $event->setData([
            'lockedParameters' => $parameters['lockedParameters'] ?? [],
            'parameters' => $parameters,
            'availableLocales' => array_keys($this->localeManager->getImplementedLocales()),
            'iconSetChoices' => array_map(function (IconSet $iconSet) {
                return $iconSet->getName(); // TODO : create a serializer
            }, $iconSets),
            'mimeTypes' => $this->iconSetManager->fetchAllResourcesMimeTypes(),
        ]);

        $event->stopPropagation();
    }
}
