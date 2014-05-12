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
class WidgetController extends BaseController
{
    /**
     * @Route("/portfolio/{id}/{type}/{action}", name="icap_portfolio_internal_widget_get", defaults={"action" = "edit"})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $action)
    {
        $response = new JsonResponse();
        $data     = array();

        if ("form" === $action) {
            /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $twigEngine */
            $twigEngine = $this->get('templating');

            $data['form'] = $twigEngine->render('IcapPortfolioBundle:templates/' . $action . ':' . $type . '.html.twig');
        }

        $response->setData($data);

        return $response;
    }
    /**
     * @Route("/portfolio/{id}/{type}", name="icap_portfolio_internal_widget_post")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, Portfolio $portfolio, $type)
    {
        $response = new JsonResponse();
        /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $twigEngine */
        $twigEngine = $this->get('templating');
        $data     = array();

        $parameters = $request->request->all();
        $data['value'] = $parameters['value'];
        $data['views'] = array(
            'view' => $twigEngine->render('IcapPortfolioBundle:templates:' . $type . '.html.twig', array('portfolioTitle' => $parameters['value']))
        );

        $response->setData($data);

        return $response;
    }
}
 