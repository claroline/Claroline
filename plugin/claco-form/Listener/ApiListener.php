<?php

namespace Claroline\ClacoFormBundle\Listener;

use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var ClacoFormManager */
    private $clacoFormManager;

    /**
     * @DI\InjectParams({
     *     "clacoFormManager" = @DI\Inject("claroline.manager.claco_form_manager")
     * })
     *
     * @param ClacoFormManager $clacoFormManager
     */
    public function __construct(ClacoFormManager $clacoFormManager)
    {
        $this->clacoFormManager = $clacoFormManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace manager of Category nodes
        $categoryCount = $this->clacoFormManager->replaceCategoryManager($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineClacoFormBundle] updated Category count: $categoryCount");

        // Replace user of Comment nodes
        $commentCount = $this->clacoFormManager->replaceCommentUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineClacoFormBundle] updated Comment count: $commentCount");

        // Replace user of Entry nodes
        $entryCount = $this->clacoFormManager->replaceEntryUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineClacoFormBundle] updated Entry count: $entryCount");

        // Replace user of EntryUser nodes
        $entryUserCount = $this->clacoFormManager->replaceEntryUserUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineClacoFormBundle] updated EntryUser count: $entryUserCount");
    }
}
