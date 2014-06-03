<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget;
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
     * @Route("/{type}/{action}", name="icap_portfolio_internal_widget_get", defaults={"action" = "empty"})
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
        else {
            $widgetNamespace = sprintf('Icap\PortfolioBundle\Entity\Widget\%sWidget', ucfirst($type));
            $widget          = new $widgetNamespace();
            $data            = $widget->getEmpty();
            $data['views']   = array();
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/{type}", name="icap_portfolio_internal_widget_post", defaults={"widgetId" = null})
     * @Route("/{type}/{widgetId}", name="icap_portfolio_internal_widget_post_id")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $widgetId)
    {
        if (null === $widgetId) {
            $widget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget')->findOneByTypeAndPortfolio($type, $portfolio);

            if (null === $widget) {
                $widgetNamespace = sprintf('Icap\PortfolioBundle\Entity\Widget\%sWidget', ucfirst($type));
                /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget $widget */
                $widget = new $widgetNamespace();
                $widget->setPortfolio($portfolio);
            }
        }
        else {
            $widget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget')->findOne($widgetId);
        }

        $data = $this->getWidgetsManager()->handle($widget, $type, $request->request->all());

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 