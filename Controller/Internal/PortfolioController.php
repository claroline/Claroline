<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/internal")
 */
class PortfolioController extends BaseController
{
    /**
     * @Route("/{id}", name="icap_portfolio_internal_portfolio", defaults={"_format" = "json"}, options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(User $loggedUser, Portfolio $portfolio)
    {
        $response = new JsonResponse();

        $data = array(
            'id'    => $portfolio->getId(),
            'slug'  => $portfolio->getSlug(),
            'title' => array(
                'view'  => sprintf('<h2 id="portfolio_title">%s</h2>', $portfolio->getTitle()),
                'value' => $portfolio->getTitle()
            )
        );

        $response->setData($data);

        return $response;
    }
}
 