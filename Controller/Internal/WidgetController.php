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
 * @Route("/internal/portfolio/{id}")
 */
class WidgetController extends BaseController
{
    /**
     * @Route("/{type}/{action}", name="icap_portfolio_internal_widget_get", defaults={"action" = "edit"})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $action)
    {
        $data = array();

        if ("form" === $action) {
            $data['form'] = $this->getWidgetsManager()->getFormView($type, $action);
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
    /**
     * @Route("/{type}", name="icap_portfolio_internal_widget_post")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, Portfolio $portfolio, $type)
    {
        $data = $this->getWidgetsManager()->handle($portfolio, $type, $request->request->all());

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 