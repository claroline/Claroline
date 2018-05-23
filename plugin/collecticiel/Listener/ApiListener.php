<?php

namespace Innova\CollecticielBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Innova\CollecticielBundle\Manager\CommentManager;
use Innova\CollecticielBundle\Manager\CorrectionManager;
use Innova\CollecticielBundle\Manager\DocumentManager;
use Innova\CollecticielBundle\Manager\DropManager;
use Innova\CollecticielBundle\Manager\NotationManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var NotationManager */
    private $notationManager;

    /** @var DocumentManager */
    private $documentManager;

    /** @var CommentManager */
    private $commentManager;

    /** @var CorrectionManager */
    private $correctionManager;

    /** @var DropManager */
    private $dropManager;

    /**
     * @DI\InjectParams({
     *     "notationManager"   = @DI\Inject("innova.manager.notation_manager"),
     *     "documentManager"   = @DI\Inject("innova.manager.document_manager"),
     *     "commentManager"    = @DI\Inject("innova.manager.comment_manager"),
     *     "correctionManager" = @DI\Inject("innova.manager.correction_manager"),
     *     "dropManager"       = @DI\Inject("innova.manager.drop_manager")
     * })
     *
     * @param NotationManager   $notationManager
     * @param DocumentManager   $documentManager
     * @param CommentManager    $commentManager
     * @param CorrectionManager $correctionManager
     * @param DropManager       $dropManager
     */
    public function __construct(
        NotationManager $notationManager,
        DocumentManager $documentManager,
        CommentManager $commentManager,
        CorrectionManager $correctionManager,
        DropManager $dropManager
    ) {
        $this->notationManager = $notationManager;
        $this->documentManager = $documentManager;
        $this->commentManager = $commentManager;
        $this->correctionManager = $correctionManager;
        $this->dropManager = $dropManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Notation nodes
        $notationCount = $this->notationManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[InnovaCollecticielBundle] updated Notation count: $notationCount");

        // Replace user of Document nodes
        $documentCount = $this->documentManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[InnovaCollecticielBundle] updated Document count: $documentCount");

        // Replace user of Comment nodes
        $commentCount = $this->commentManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[InnovaCollecticielBundle] updated Comment count: $commentCount");

        // Replace user of Correction nodes
        $correctionCount = $this->correctionManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[InnovaCollecticielBundle] updated Correction count: $correctionCount");

        // Replace user of Drop nodes
        $dropCount = $this->dropManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[InnovaCollecticielBundle] updated Drop count: $dropCount");
    }
}
