<?php

namespace Claroline\DropZoneBundle\Crud;

use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\DropZoneBundle\Manager\DropzoneManager;

class Dropzone
{
    /**
     * WorkspaceCrud constructor.
     *
     * @param WorkspaceManager $manager
     */
    public function __construct(DropzoneManager $dropzoneManager)
    {
        $this->dropzoneManager = $dropzoneManager;
    }

    /**
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
