<?php

namespace Icap\DropzoneBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\DropzoneBundle\Manager\CorrectionManager;
use Icap\DropzoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var DropzoneManager */
    private $dropManager;

    /** @var CorrectionManager */
    private $correctionManager;

    /**
     * @DI\InjectParams({
     *     "dropManager"       = @DI\Inject("icap.manager.dropzone_manager"),
     *     "correctionManager" = @DI\Inject("icap.manager.correction_manager")
     * })
     *
     * @param DropManager       $dropManager
     * @param CorrectionManager $correctionManager
     */
    public function __construct(DropzoneManager $dropManager, CorrectionManager $correctionManager)
    {
        $this->dropManager = $dropManager;
        $this->correctionManager = $correctionManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Drop nodes
        $dropCount = $this->dropManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapDropzoneBundle] updated Drop count: $dropCount");

        // Replace user of Correction nodes
        $correctionCount = $this->correctionManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapDropzoneBundle] updated Correction count: $correctionCount");
    }
}
