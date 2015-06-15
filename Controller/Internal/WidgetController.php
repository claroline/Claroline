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
            $widget = $this->getWidgetsManager()->getNewDataWidget($type);
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

        $widgets = $this->getWidgetsManager()->getWidgets();

        $data = [];

        foreach ($widgets as $widget) {
            $data[] = $this->getWidgetsManager()->getWidgetData($widget);
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
 