<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\IconSetManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class AppearanceListener
{
    /** @var ParametersSerializer */
    private $serializer;

    /** @var IconSetManager */
    private $iconSetManager;

    /**
     * AppearanceListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer"     = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer"),
     *     "iconSetManager" = @DI\Inject("claroline.manager.icon_set_manager")
     * })
     *
     * @param ParametersSerializer $serializer
     * @param IconSetManager       $iconSetManager
     */
    public function __construct(
        ParametersSerializer $serializer,
        IconSetManager $iconSetManager
    ) {
        $this->serializer = $serializer;
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

        $event->setData([
            'parameters' => $this->serializer->serialize(),
            'iconSetChoices' => array_map(function (IconSet $iconSet) {
                return $iconSet->getName(); // TODO : create a serializer
            }, $iconSets),
        ]);

        $event->stopPropagation();
    }
}
