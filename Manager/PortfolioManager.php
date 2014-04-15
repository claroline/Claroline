<?php

namespace Icap\PortfolioBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioUser;
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
        $this->persistortfolio($portfolio);
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

        $this->persistortfolio($portfolio);
    }

    /**
     * @param Portfolio                  $portfolio
     * @param Collection|PortfolioUser[] $originalPortfolioUsers
     */
    public function updateVisibility(Portfolio $portfolio, Collection $originalPortfolioUsers)
    {
        $portfolioUsers = $portfolio->getPortfolioUsers();

        foreach ($portfolioUsers as $portfolioUser) {
            if ($originalPortfolioUsers->contains($portfolioUser)) {
                $originalPortfolioUsers->removeElement($portfolioUser);
            }
        }

        // Delete rules
        foreach ($originalPortfolioUsers as $originalPortfolioUser) {
            $this->entityManager->remove($originalPortfolioUser);
        }

        $this->persistortfolio($portfolio);
    }

    /**
     * @param Portfolio $portfolio
     */
    private function persistortfolio(Portfolio $portfolio)
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }
}