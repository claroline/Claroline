<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Event\Log\PortfolioViewEvent;
use Icap\PortfolioBundle\Form\Type\AnalyticsViewsType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_portfolio.manager.analytics")
 */
class AnalyticsManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TokenStorage
     */
    private $securityTokenStorage;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator" = @DI\Inject("translator"),
     *     "securityTokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(EntityManager $entityManager, TranslatorInterface $translator, TokenStorage $securityTokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     * @param bool|false $withPortfolioSelect
     *
     * @return AnalyticsViewsType
     */
    public function getAnalyticsViewsForm($withPortfolioSelect = false)
    {
        return new AnalyticsViewsType($this->translator, $this->securityTokenStorage->getToken()->getUser(), $withPortfolioSelect);
    }

    /**
     * @return array
     */
    public function getDefaultRangeForViews()
    {
        $startDate = new \DateTime('now');
        $startDate->sub(new \DateInterval('P29D'));

        $endDate = new \DateTime('now');

        return [$startDate->getTimestamp(), $endDate->getTimestamp()];
    }

    /**
     * @param User      $user
     * @param Portfolio $portfolio
     * @param array     $range     0 => startDate and 1 => endDate
     *
     * @return array
     */
    public function getViewsForChart(User $user, Portfolio $portfolio, array $range)
    {
        /** @var \Claroline\CoreBundle\Repository\Log\LogRepository $logRepository */
        $logRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Log\Log');

        $queryBuilder = $logRepository
            ->createQueryBuilder('log')
            ->select('log.shortDateLog as shortDate, count(log.id) as total')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate')
        ;

        $queryBuilder = $logRepository->addOwnerFilterToQueryBuilder($queryBuilder, $user);
        $queryBuilder = $logRepository->addActionFilterToQueryBuilder($queryBuilder, PortfolioViewEvent::ACTION);
        $queryBuilder = $logRepository->addOtherElementIdFilterToQueryBuilder($queryBuilder, $portfolio->getId());
        $queryBuilder = $logRepository->addDateRangeFilterToQueryBuilder($queryBuilder, $range);

        return $logRepository->extractChartData($queryBuilder->getQuery()->getResult(), $range);
    }
}
