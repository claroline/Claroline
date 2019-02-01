<?php

namespace Claroline\OpenBadgeBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.open_badge.badge")
 * @DI\Tag("claroline.crud")
 */
class BadgeClassCrud
{
    /** @var ParametersSerializer */
    private $parametersSerializer;

    /**
     * TemplateSerializer constructor.
     *
     * @DI\InjectParams({
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param ParametersSerializer $parametersSerializer
     */
    public function __construct(
          ParametersSerializer $parametersSerializer
      ) {
        $this->parametersSerializer = $parametersSerializer;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_openbadgebundle_entity_badgeclass")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $badge = $event->getObject();

        if ($badge->getWorkspace()) {
            $badge->setEnabled($this->parametersSerializer->serialize()['badges']['enable_default']);
        }
    }
}
