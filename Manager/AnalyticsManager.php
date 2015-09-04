<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioComment;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Factory\CommentFactory;
use Icap\PortfolioBundle\Form\Type\AnalyticsViewsType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;
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
     *
     * @return array
     */
    public function getViewsForChart(User $user, Portfolio $portfolio)
    {
        /** @var \Claroline\CoreBundle\Repository\Log\LogRepository $logRepository */
        $logRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Log\Log');
        return $logRepository->createQueryBuilder('l')
            ->select("COUNT(l.id) as nbLog, l.shortDateLog")
            ->where("l.owner = :owner")
            ->andWhere("l.otherElementId = :otherElementId")
            ->setParameters([
                'owner' => $user,
                'otherElementId' => $portfolio->getId()
            ])
            ->groupBy("l.shortDateLog")
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
