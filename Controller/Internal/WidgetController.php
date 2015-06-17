<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Widget;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/internal/portfolio/widget")
 */
class WidgetController extends BaseController
{
    /**
     * @Route("/{type}/{action}", name="icap_portfolio_internal_widget_get", defaults={"action" = "empty"})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(Request $request, User $loggedUser, $type, $action)
    {
        $this->checkPortfolioToolAccess();

        $data = [];

        if ("form" === $action) {
            $data['form'] = $this->getWidgetsManager()->getFormView($type, $action);
        }
        else {
            $widget = $this->getWidgetsManager()->getNewDataWidget($type, $loggedUser);
            $data = $this->getWidgetsManager()->getWidgetData($widget);
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("", name="icap_portfolio_internal_widget", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAllAction(Request $request, User $loggedUser)
    {
        $this->checkPortfolioToolAccess();

        $widgets = $this->getWidgetsManager()->getWidgets($loggedUser);

        $data = [];

        foreach ($widgets as $widget) {
            $data[] = $this->getWidgetsManager()->getWidgetData($widget);
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/{type}/{widgetId}", name="icap_portfolio_internal_widget_delete", requirements={"widgetId" = "\d+"})
     * @Method({"DELETE"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function deleteAction(Request $request, User $loggedUser, $type, $widgetId)
    {
        $this->checkPortfolioToolAccess($loggedUser);

        /** @var \Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository $abstractWidgetRepository */
        $abstractWidgetRepository = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget');

        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget $widget */
        $widget = $abstractWidgetRepository->findOneByWidgetType($type, $widgetId, $loggedUser);

        $response = new JsonResponse();

        try {
            $this->getWidgetsManager()->deleteDataWidget($widget);

        } catch(\Exception $exception){
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * @Route("/{type}", name="icap_portfolio_internal_widget_post")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, $type)
    {
        $this->checkPortfolioToolAccess($loggedUser);

        $widgetManager = $this->getWidgetsManager();

        try {
            $newWidget = $widgetManager->getNewDataWidget($type, $loggedUser);
            $data = $widgetManager->handle($newWidget, $type, $request->request->all(), $this->get('kernel')->getEnvironment());
            $statusCode = Response::HTTP_CREATED;
        } catch(\Exception $exception){
            echo "<pre>";
            var_dump($exception->getMessage());
            echo "</pre>" . PHP_EOL;
            $data = [];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response = new JsonResponse();
        $response
            ->setData($data)
            ->setStatusCode($statusCode);

        return $response;
    }

    /**
     * @Route("/{type}/{widgetId}", name="icap_portfolio_internal_widget_put", requirements={"widgetId" = "\d+"})
     * @Method({"PUT"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function putAction(Request $request, User $loggedUser, $type, $widgetId)
    {
        $this->checkPortfolioToolAccess($loggedUser);

        /** @var \Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository $abstractWidgetRepository */
        $abstractWidgetRepository = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\AbstractWidget');

        $widget = $abstractWidgetRepository->findOneByWidgetType($type, $widgetId, $loggedUser);

        $data = $this->getWidgetsManager()->handle($widget, $type, $request->request->all(), $this->get('kernel')->getEnvironment());

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 