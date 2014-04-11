<?php

namespace Icap\PortfolioBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.manager.portfolio")
 */
class PortfolioManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
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
     */
    public function addPortfolio(Portfolio $portfolio)
    {
        $this->persist($portfolio);
    }

    /**
     * @param Portfolio $portfolio
     * @param bool      $refreshUrl
     */
    public function renamePortfolio(Portfolio $portfolio, $refreshUrl = false)
    {
        if ($refreshUrl) {
            $portfolio->setSlug(null);
        }

        $this->persist($portfolio);
    }

    private function persist(Portfolio $portfolio)
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }
}