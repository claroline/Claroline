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
     * @Route("/portfolio/{id}/{type}/{action}", name="icap_portfolio_internal_widget", defaults={"action" = "edit"})
     * @Method({"GET", "POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $action)
    {
        $response = new JsonResponse();
        /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $twigEngine */
        $twigEngine = $this->get('templating');
        $data     = array();

        if ($request->isMethod("POST")) {
            $parameters = $request->request->all();
            $data['value'] = $parameters['value'];
            $data['views'] = array(
                'view' => $twigEngine->render('IcapPortfolioBundle:templates:' . $type . '.html.twig', array('portfolioTitle' => $parameters['value']))
            );
        }
        else {
            if ("form" === $action) {

                $data['form'] = $twigEngine->render('IcapPortfolioBundle:templates/' . $action . ':' . $type . '.html.twig');
            }
        }

        $response->setData($data);

        return $response;
    }
}
 