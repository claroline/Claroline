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

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $portfolioGuides = $this->entityManager->getRepository('IcapPortfolioBundle:PortfolioGuide')->findByUser($from);

        if (count($portfolioGuides) > 0) {
            foreach ($portfolioGuides as $portfolioGuide) {
                $portfolioGuide->setUser($to);
            }

            $this->entityManager->flush();
        }

        return count($portfolioGuides);
    }
}
