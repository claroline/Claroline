<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;

class TechnicalListener
{
    /** @var ObjectManager */
    private $om;

    /** @var ParametersSerializer */
    private $serializer;

    /**
     * TechnicalListener constructor.
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
