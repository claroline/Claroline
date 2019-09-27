<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class TechnicalListener
{
    /** @var ObjectManager */
    private $om;

    /** @var ParametersSerializer */
    private $serializer;

    /**
     * TechnicalListener constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer")
     * })
     *
     * @param ObjectManager        $om
     * @param ParametersSerializer $serializer
     */
    public function __construct(
        ObjectManager $om,
        ParametersSerializer $serializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * Displays technical administration tool.
     *
     * @DI\Observe("administration_tool_technical_settings")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $event->setData([
            'parameters' => $this->serializer->serialize(),
            'tools' => array_map(function (AdminTool $tool) {
                return $tool->getName();
            }, $this->om->getRepository(AdminTool::class)->findAll()),
        ]);

        $event->stopPropagation();
    }
}
