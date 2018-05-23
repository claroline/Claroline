<?php

namespace Claroline\DropZoneBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var DropzoneManager */
    private $dropzoneManager;

    /**
     * @DI\InjectParams({
     *     "dropzoneManager" = @DI\Inject("claroline.manager.dropzone_manager")
     * })
     *
     * @param DropzoneManager $dropzoneManager
     */
    public function __construct(DropzoneManager $dropzoneManager)
    {
        $this->dropzoneManager = $dropzoneManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Drop nodes
        $dropCount = $this->dropzoneManager->replaceDropUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineDropZoneBundle] updated Drop count: $dropCount");

        // Replace user of Correction nodes
        $correctionCount = $this->dropzoneManager->replaceCorrectionUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineDropZoneBundle] updated Correction count: $correctionCount");

        // Replace user of Document nodes
        $documentCount = $this->dropzoneManager->replaceDocumentUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineDropZoneBundle] updated Document count: $documentCount");
    }
}
