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
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/{type}", name="icap_portfolio_internal_widget_post")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, Portfolio $portfolio, $type)
    {
        $response      = new JsonResponse();
        $widgetManager = $this->getWidgetsManager();
        $widgetsConfig = $widgetManager->getWidgetsConfig();
        $data          = array();
        $statusCode    = Response::HTTP_BAD_REQUEST;

        if (isset($widgetsConfig[$type])) {
            if (!$widgetsConfig[$type]['isUnique'] || (
                    $widgetsConfig[$type]['isUnique']
                    && null === $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget')->findOneByTypeAndPortfolio($type, $portfolio)
                )) {
                $newWidget  = $widgetManager->getNewWidget($portfolio, $type);
                $data       = $widgetManager->handle($newWidget, $type, $request->request->all());
                $statusCode = Response::HTTP_OK;
            }
        }

        $response
            ->setData($data)
            ->setStatusCode($statusCode);

        return $response;
    }

    /**
     * @Route("/{type}", name="icap_portfolio_internal_widget_put", defaults={"widgetId" = null})
     * @Route("/{type}/{widgetId}", name="icap_portfolio_internal_widget_put_id")
     * @Method({"PUT"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function putAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $widgetId)
    {
        /** @var \Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository $abstractWidgetRepository */
        $abstractWidgetRepository = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget');

        if (null === $widgetId) {
            $widget = $abstractWidgetRepository->findOneByTypeAndPortfolio($type, $portfolio);
        }
        else {
            $widget = $abstractWidgetRepository->findOne($widgetId);
        }

        $data = $this->getWidgetsManager()->handle($widget, $type, $request->request->all());

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/{type}", name="icap_portfolio_internal_widget_delete", defaults={"widgetId" = null})
     * @Route("/{type}/{widgetId}", name="icap_portfolio_internal_widget_delete_id")
     * @Method({"DELETE"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function deleteAction(Request $request, User $loggedUser, Portfolio $portfolio, $type, $widgetId)
    {
        /** @var \Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository $abstractWidgetRepository */
        $abstractWidgetRepository = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget');

        if (null === $widgetId) {
            $widget = $abstractWidgetRepository->findOneByTypeAndPortfolio($type, $portfolio);
        }
        else {
            $widget = $abstractWidgetRepository->findOne($widgetId);
        }

        $response = new JsonResponse();

        try {
            $this->getWidgetsManager()->deleteWidget($widget);

        } catch(\Exception $exception){
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
 