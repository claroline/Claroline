<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioGuide;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.manager.portfolio_guide")
 */
class PortfolioGuideManager
{
    const PORTFOLIO_OPENING_MODE_VIEW = 'view';
    const PORTFOLIO_OPENING_MODE_EVALUATE = 'evaluate';
    const PORTFOLIO_OPENING_MODE_EDIT = 'edit';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Portfolio $portfolio
     * @param User      $user
     *
     * @return null|PortfolioGuide
     */
    public function getByPortfolioAndGuide(Portfolio $portfolio, User $user)
    {
        $portfolioGuide = $this->entityManager->getRepository('IcapPortfolioBundle:PortfolioGuide')->findByPortfolioAndUser($portfolio, $user);

        return $portfolioGuide;
    }

    /**
     * @param PortfolioGuide $portfolioGuide
     */
    public function updateCommentsViewDate(PortfolioGuide $portfolioGuide)
    {
        $portfolioGuide->setCommentsViewAt(new \DateTime());

        $this->entityManager->flush($portfolioGuide);
    }
}
