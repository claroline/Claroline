<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Icap\BadgeBundle\Manager\BadgeManager;
use Claroline\CoreBundle\Rule\Validator;
use Icap\BadgeBundle\Entity\Badge;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class WidgetPickerController extends BaseController
{
    /**
     * @Route("/internal/portfolio/widgets", name="icap_portfolio_widget_picker", options={"expose": true})
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("IcapPortfolioBundle:widget:widgetPicker.html.twig")
     */
    public function widgetPickerAction(Request $request, User $user)
    {
        /** @var ParameterBag $requestParameters */
        $requestParameters = $request->request;

        /** @var \Icap\PortfolioBundle\Manager\WidgetsManager $widgetManager */
        $widgetManager = $this->get('icap_portfolio.manager.widgets');

        $parameters = array(
            'user' => $user,
            'type' => $requestParameters->get('type', null),
            'multiple' => $requestParameters->get('multiple', true)
        );

        $widgets = $widgetManager->getWidgetsForWidgetPicker($parameters);

        $value = $requestParameters->get('value', array());

        if (!is_array($value)) {
            $value = array($value);
        }

        return array(
            'widgets' => $widgets,
            'multiple' => $parameters['multiple'],
            'value' => $value
        );
    }
}
