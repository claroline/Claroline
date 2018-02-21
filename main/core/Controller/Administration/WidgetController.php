<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Form\Administration\WidgetEditType;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('roles_management')")
 */
class WidgetController extends Controller
{
    private $formFactory;
    private $om;
    private $request;
    private $roleManager;
    private $translator;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"    = @DI\Inject("translator"),
     *     "widgetManager" = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        WidgetManager $widgetManager
    ) {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/widgets/management",
     *     name="claro_admin_widgets_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function widgetsManagementAction()
    {
        $widgets = $this->widgetManager->getAll();
        $toOrder = [];

        foreach ($widgets as $widget) {
            $widgetName = $this->translator->trans($widget->getName(), [], 'widget');
            $toOrder[$widgetName] = $widget;
        }
        ksort($toOrder);

        return ['widgets' => $toOrder];
    }

    /**
     * @EXT\Route(
     *     "widget/{widget}/edit/form",
     *     name="claro_widget_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Administration\Widget:widgetEditModalForm.html.twig")
     *
     * @param Widget $widget
     */
    public function widgetEditFormAction(Widget $widget)
    {
        $form = $this->formFactory->create(new WidgetEditType(), $widget);

        return ['form' => $form->createView(), 'widget' => $widget];
    }

    /**
     * @EXT\Route(
     *     "widget/{widget}/edit",
     *     name="claro_widget_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Administration\Widget:widgetEditModalForm.html.twig")
     *
     * @param Widget $widget
     */
    public function widgetEditAction(Widget $widget)
    {
        $form = $this->formFactory->create(new WidgetEditType(), $widget);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->widgetManager->persistWidget($widget);

            return new JsonResponse('success', 200);
        } else {
            return ['form' => $form->createView(), 'widget' => $widget];
        }
    }
}
