<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\PortfolioBundle\Manager\CommentsManager;
use Icap\PortfolioBundle\Manager\PortfolioGuideManager;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use Icap\PortfolioBundle\Manager\PortfolioUserManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var CommentsManager */
    private $portfolioCommentManager;

    /** @var PortfolioManager */
    private $portfolioManager;

    /** @var PortfolioUserManager */
    private $portfolioUserManager;

    /** @var PortfolioGuideManager */
    private $portfolioGuideManager;

    /**
     * @DI\InjectParams({
     *     "portfolioCommentManager" = @DI\Inject("icap_portfolio.manager.comments"),
     *     "portfolioManager"        = @DI\Inject("icap_portfolio.manager.portfolio"),
     *     "portfolioUserManager"    = @DI\Inject("icap_portfolio.manager.portfolio_user"),
     *     "portfolioGuideManager"   = @DI\Inject("icap_portfolio.manager.portfolio_guide")
     * })
     *
     * @param CommentsManager       $portfolioCommentManager
     * @param PortfolioManager      $portfolioManager
     * @param PortfolioUserManager  $portfolioUserManager
     * @param PortfolioGuideManager $portfolioGuideManager
     */
    public function __construct(
        CommentsManager $portfolioCommentManager,
        PortfolioManager $portfolioManager,
        PortfolioUserManager $portfolioUserManager,
        PortfolioGuideManager $portfolioGuideManager)
    {
        $this->portfolioCommentManager = $portfolioCommentManager;
        $this->portfolioManager = $portfolioManager;
        $this->portfolioUserManager = $portfolioUserManager;
        $this->portfolioGuideManager = $portfolioGuideManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of PortfolioComment nodes
        $pdfportfolioCommentCount = $this->portfolioCommentManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapPortfolioBundle] updated PortfolioComment count: $pdfportfolioCommentCount");

        // Replace user of Portfolio nodes
        $portfolioCount = $this->portfolioManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapPortfolioBundle] updated Portfolio count: $portfolioCount");

        // Replace user of PortfolioUser nodes
        $portfolioUserCount = $this->portfolioUserManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapPortfolioBundle] updated PortfolioUser count: $portfolioUserCount");

        // Replace user of PortfolioGuide nodes
        $portfolioGuideCount = $this->portfolioGuideManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapPortfolioBundle] updated PortfolioGuide count: $portfolioGuideCount");
    }
}
