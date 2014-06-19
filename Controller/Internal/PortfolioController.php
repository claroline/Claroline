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
     * @Route("/portfolio/{id}", name="icap_portfolio_internal_portfolio", options={"expose"=true})
     * @Method({"GET"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function getAction(User $loggedUser, Portfolio $portfolio)
    {
        $response = new JsonResponse();

        /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $twigEngine */
        $twigEngine = $this->get('templating');

        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[] $widgets */
        $widgets = $portfolio->getWidgets();

        $data = array(
            'id'          => $portfolio->getId(),
            'disposition' => $portfolio->getDisposition()
        );

        foreach ($widgets as $key => $widget) {
            $widgetType = $widget->getWidgetType();

            $widgetViews = array(
                'type'   => $widgetType,
                'column' => $widget->getColumn(),
                'row'    => $widget->getRow(),
                'views'  => array(
                    'view' => $this->getWidgetsManager()->getView($widget, $widgetType)
                )
            );

            $widgetDatas       = $widgetViews + $widget->getData();
            $data['widgets'][] = $widgetDatas;
        }

        $response->setData($data);

        return $response;
    }
}
 