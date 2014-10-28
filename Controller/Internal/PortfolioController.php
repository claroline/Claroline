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

/**
 * @Route("/internal")
 */
class PortfolioController extends BaseController
{
    /**
     * @Route("/portfolio/{id}", name="icap_portfolio_internal_portfolio", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        $response = new JsonResponse();
        $response->setData($this->getPortfolioManager()->getPortfolioData($portfolio));

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

        $data = $this->getPortfolioManager()->handle($portfolio, $request->request->all(), $this->get('kernel')->getEnvironment());

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 