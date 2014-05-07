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
class WidgetController extends BaseController
{
    /**
     * @Route("/portfolio/{id}/{type}/{action}", name="icap_portfolio_internal_widget_form", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function widgetAction(User $loggedUser, Portfolio $portfolio, $type, $action)
    {
        $response = new JsonResponse();
        $data     = array();

        /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $twigEngine */
        $twigEngine = $this->get('templating');

        $data['form'] = $twigEngine->render('IcapPortfolioBundle:templates/' . $action . ':' . $type . '.html.twig');

        $response->setData($data);

        return $response;
    }
}
 