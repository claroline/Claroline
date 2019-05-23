<?php

namespace Claroline\DropZoneBundle\Crud;

use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.claroline_dropzone")
 * @DI\Tag("claroline.crud")
 */
class Dropzone
{
    /**
     * WorkspaceCrud constructor.
     *
     * @DI\InjectParams({
     *     "dropzoneManager" = @DI\Inject("claroline.manager.dropzone_manager"),
     * })
     *
     * @param WorkspaceManager $manager
     */
    public function __construct(DropzoneManager $dropzoneManager)
    {
        $this->dropzoneManager = $dropzoneManager;
    }

    /**
     * @DI\Observe("crud_post_update_object_claroline_dropzonebundle_entity_dropzone")
     *
     * @param UpdateEvent $event
     */
    public function postUpdate(UpdateEvent $event)
    {
        $dropzone = $event->getObject();
        $oldDatas = $event->getOldData();

        if ($oldDatas['parameters']['scoreMax'] !== $dropzone->getScoreMax()) {
            $this->dropzoneManager->updateScoreByScoreMax($dropzone, $oldDatas['parameters']['scoreMax'], $dropzone->getScoreMax());
        }
    }
}
