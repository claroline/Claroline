<?php

namespace Icap\PortfolioBundle\Factory;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioComment;
use Icap\PortfolioBundle\Manager\WidgetTypeManager;
use Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.factory.comment")
 */
class CommentFactory
{
    /**
     * @param Portfolio $portfolio
     * @param User      $user
     *
     * @return \Icap\PortfolioBundle\Entity\PortfolioComment
     */
    public function createComment(Portfolio $portfolio, User $user)
    {
        $comment = new PortfolioComment();
        $comment
            ->setPortfolio($portfolio)
            ->setSender($user)
            ->setSendingDate(new \DateTime());

        return $comment;
    }
}
 