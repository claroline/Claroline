<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Pager\PagerFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioUser;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\WidgetNode;
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
     * @var \Claroline\CoreBundle\Pager\PagerFactory
     */
    protected $pagerFactory;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "pagerFactory"  = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(EntityManager $entityManager, PagerFactory $pagerFactory)
    {
        $this->entityManager = $entityManager;
        $this->pagerFactory  = $pagerFactory;
    }

    /**
     * @param User $user
     *
     * @param int  $page
     *
     * @return array
     */
    public function getListPaginate(User $user, $page = 1)
    {
        $portfolios = array();

        $query = $this->entityManager->getRepository('IcapPortfolioBundle:Portfolio')->findByUser($user, false);
        $pager = $this->pagerFactory->createPager($query, $page, 10);

        return $portfolios;
    }

    /**
     * @param Portfolio   $portfolio
     * @param TitleWidget $titleWidget
     *
     * @throws \InvalidArgumentException
     */
    public function addPortfolio(Portfolio $portfolio, TitleWidget $titleWidget)
    {
        $titleWidgetType = $this->entityManager->getRepository('IcapPortfolioBundle:Widget\WidgetType')->findOneByName('title');
        if (null === $titleWidget) {
            throw new \InvalidArgumentException("Unknow widget type 'title'.");
        }

        $widgetNode = new WidgetNode();
        $widgetNode
            ->setPortfolio($portfolio)
            ->setWidgetType($titleWidgetType);

        $titleWidget->setWidgetNode($widgetNode);

        $this->entityManager->persist($widgetNode);
        $this->entityManager->persist($titleWidget);

        $this->persistPortfolio($portfolio);
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

        $this->persistPortfolio($portfolio);
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

        $this->persistPortfolio($portfolio);
    }

    /**
     * @param Portfolio $portfolio
     */
    private function persistPortfolio(Portfolio $portfolio)
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }
}