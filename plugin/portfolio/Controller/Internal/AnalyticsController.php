<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/internal/portfolio/analytics/{id}")
 */
class AnalyticsController extends BaseController
{
    /**
     * @Route("/views/{startDate}/{endDate}", name="icap_portfolio_internal_analytics_views")
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getViewAction(User $loggedUser, Portfolio $portfolio, $startDate, $endDate)
    {
        $this->checkPortfolioToolAccess($loggedUser, $portfolio);

        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        $chartData = $this->getAnalyticsManager()->getViewsForChart(
            $loggedUser,
            $portfolio,
            [$startDate->getTimestamp(), $endDate->getTimestamp()]
        );
        $response = new JsonResponse($chartData, Response::HTTP_OK);

        return $response;
    }
}
