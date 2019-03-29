<?php

namespace Claroline\CoreBundle\API\Crud\Home;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.widget_container")
 * @DI\Tag("claroline.crud")
 */
class WidgetContainerCrud
{
    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "instanceCrud" = @DI\Inject("claroline.crud.widget_instance")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, WidgetInstanceCrud $instanceCrud)
    {
        $this->om = $om;
        $this->instanceCrud = $instanceCrud;
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_corebundle_entity_widget_widgetcontainer")
     *
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $container = $event->getObject();

        $this->delete($container);
    }

    public function delete($container)
    {
        foreach ($container->getInstances() as $instance) {
            $this->instanceCrud->delete($instance);
            $this->om->remove($instance);
        }

        foreach ($container->getWidgetContainerConfigs() as $config) {
            $this->om->remove($config);
        }

        $this->om->flush();
    }
}
