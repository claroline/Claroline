<?php

namespace Icap\PortfolioBundle\Factory;

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
     *
     * @return \Icap\PortfolioBundle\Entity\PortfolioComment
     */
    public function createComment(Portfolio $portfolio)
    {
        $comment = new PortfolioComment();
        $comment->setPortfolio($portfolio);

        return $comment;
    }
}
 