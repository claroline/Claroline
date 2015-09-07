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

use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;

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
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("claroline.form.factory"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "widgetManager" = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        RoleManager $roleManager,
        WidgetManager $widgetManager
    )
    {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
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
     *
     */
    public function widgetsManagementAction()
    {
        return array();
    }
}
