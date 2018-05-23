<?php

namespace FormaLibre\SupportBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var SupportManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("formalibre.manager.support_manager")
     * })
     *
     * @param SupportManager $manager
     */
    public function __construct(SupportManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Comment nodes
        $commentCount = $this->manager->replaceCommentUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibreSupportBundle] updated Comment count: $commentCount");

        // Replace user of TicketUser nodes
        $ticketUserCount = $this->manager->replaceTicketUserUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibreSupportBundle] updated TicketUser count: $ticketUserCount");

        // Replace user of Ticket nodes
        $ticketCount = $this->manager->replaceTicketUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibreSupportBundle] updated Ticket count: $ticketCount");

        // Replace user of Intervention nodes
        $interventionCount = $this->manager->replaceInterventionUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibreSupportBundle] updated Intervention count: $interventionCount");
    }
}
