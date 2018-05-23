<?php

namespace Icap\WikiBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\WikiBundle\Manager\ContributionManager;
use Icap\WikiBundle\Manager\SectionManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var SectionManager */
    private $sectionManager;

    /** @var ContributionManager */
    private $contributionManager;

    /**
     * @DI\InjectParams({
     *     "sectionManager"      = @DI\Inject("icap.wiki.section_manager"),
     *     "contributionManager" = @DI\Inject("icap.wiki.contribution_manager")
     * })
     *
     * @param SectionManager      $sectionManager
     * @param ContributionManager $contributionManager
     */
    public function __construct(SectionManager $sectionManager, ContributionManager $contributionManager)
    {
        $this->sectionManager = $sectionManager;
        $this->contributionManager = $contributionManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Section nodes
        $sectionCount = $this->sectionManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapWikiBundle] updated Section count: $sectionCount");

        // Replace user of Contribution nodes
        $contributionCount = $this->contributionManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapWikiBundle] updated Contribution count: $contributionCount");
    }
}
