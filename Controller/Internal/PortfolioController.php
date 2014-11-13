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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/internal")
 */
class PortfolioController extends BaseController
{
    /**
     * @Route("/portfolio", name="icap_portfolio_internal_portfolios", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAllAction(User $loggedUser)
    {
        $this->checkPortfolioToolAccess();

        $response = new JsonResponse($this->getPortfolioManager()->getUserGuidedPortfoliosData($loggedUser));

        return $response;
    }
    /**
     * @Route("/portfolio/{id}", name="icap_portfolio_internal_portfolio", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        if ($portfolio->getUser() !== $loggedUser && !$portfolio->hasGuide($loggedUser)) {
            throw new NotFoundHttpException();
        }

        $data = $this->getPortfolioManager()->getPortfolioData($portfolio);

        $portfolioGuide = $this->getPortfolioGuideManager()->getByPortfolioAndGuide($portfolio, $loggedUser);

        if (null !== $portfolioGuide) {
            $data['unreadComments'] = $portfolio->getCountUnreadComments($portfolioGuide->getCommentsViewAt());
            $data['commentsViewAt'] = $portfolioGuide->getCommentsViewAt();
        }

        $response = new JsonResponse($data);

        return $response;
    }

    /**
     * @Route("/portfolio/{id}", name="icap_portfolio_internal_portfolio_put")
     * @Method({"PUT"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function putAction(Request $request, User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        if ($portfolio->getUser() === $loggedUser) {
            $data = $this->getPortfolioManager()->handle($portfolio, $request->request->all(), $this->get('kernel')->getEnvironment());
        }
        else {
            $portfolioGuide = $this->getPortfolioGuideManager()->getByPortfolioAndGuide($portfolio, $loggedUser);

            if (null !== $portfolioGuide) {
                $this->getPortfolioGuideManager()->updateCommentsViewDate($portfolioGuide);
                $data = $this->getPortfolioManager()->getPortfolioData($portfolio);

                $data['unreadComments'] = $portfolio->getCountUnreadComments($portfolioGuide->getCommentsViewAt());
                $data['commentsViewAt'] = $portfolioGuide->getCommentsViewAt();
            }
            else {
                throw new NotFoundHttpException();
            }
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 