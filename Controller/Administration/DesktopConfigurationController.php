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

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('desktop_and_home')")
 */
class DesktopConfigurationController extends Controller
{
    private $desktopAdminTool;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        ToolManager $toolManager
    )
    {
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/desktop/configuration/menu",
     *     name="claro_admin_desktop_configuration_menu",
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     *
     * Displays the desktop configuration menu.
     *
     * @return Response
     */
    public function adminDesktopConfigMenuAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/desktop/tools/configure/type/{type}",
     *     name="claro_admin_desktop_tools_configuration",
     *     defaults={"type"=0}
     * )
     *
     * @EXT\Template()
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser"=true})
     *
     * Displays the desktop tools configuration page in admin.
     *
     * @param int type
     * @return Response
     */
    public function adminDesktopConfigureToolAction($type = 0)
    {
        $menuType = intval($type);
        $tools = $this->toolManager->getDesktopToolsConfigurationArrayForAdmin($menuType);
        $orderedTools = $this->toolManager
            ->getConfigurableDesktopOrderedToolsByTypeForAdmin($menuType);

        return array(
            'tools' => $tools,
            'orderedTools' => $orderedTools,
            'type' => $menuType
        );
    }

    /**
     * @EXT\Route(
     *     "/ordered/tool/{orderedTool}/visibility/toggle",
     *     name="claro_admin_ordered_tool_toggle_visibility",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser"=true})
     */
    public function toggleVisibility(OrderedTool $orderedTool)
    {
        $isVisible = $orderedTool->isVisibleInDesktop();
        $orderedTool->setVisibleInDesktop(!$isVisible);
        $this->toolManager->editOrderedTool($orderedTool);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/ordered/tool/{orderedTool}/locke/toggle",
     *     name="claro_admin_ordered_tool_toggle_lock",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser"=true})
     */
    public function toggleLock(OrderedTool $orderedTool)
    {
        $isLocked = $orderedTool->isLocked();
        $orderedTool->setLocked(!$isLocked);
        $this->toolManager->editOrderedTool($orderedTool);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/tools/order/update/admin/ordered/tool/{orderedTool}/type/{type}/next/{nextOrderedToolId}",
     *     name="claro_admin_desktop_update_ordered_tool_order",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param OrderedTool $orderedTool
     * @param int type
     * @param int nextOrderedToolId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateOrderedToolOrderAction(
        OrderedTool $orderedTool,
        $nextOrderedToolId,
        $type = 0
    )
    {
        if (is_null($orderedTool->getUser()) && $orderedTool->getType() === intval($type)) {

            $this->toolManager->reorderAdminOrderedTool(
                $orderedTool,
                $nextOrderedToolId,
                $type
            );

            return new Response('success', 200);
        } else {

            throw new AccessDeniedException();
        }
    }
}
