<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/internal/portfolio/analytics/{id}")
 */
class AnalyticsController extends BaseController
{
    /**
     * @Route("/views", name="icap_portfolio_internal_analytics_views")
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getViewAction(User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess($loggedUser, $portfolio);

        $response = new JsonResponse($this->getAnalyticsManager()->getViewsForChart($loggedUser, $portfolio), Response::HTTP_OK);

        return $response;
    }
}
