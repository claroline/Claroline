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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DesktopConfigurationController extends Controller
{
    private $desktopAdminTool;
    private $securityContext;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ToolManager $toolManager
    )
    {
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->desktopAdminTool = $this->toolManager->getAdminToolByName('desktop_and_home');
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
        $this->checkOpen();

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
        $this->checkOpen();

        $tools = $this->toolManager->getDesktopToolsConfigurationArrayForAdmin($type);
        $orderedTools = $this->toolManager
            ->getConfigurableDesktopOrderedToolsByTypeForAdmin($type);

        return array(
            'tools' => $tools,
            'orderedTools' => $orderedTools,
            'type' => $type
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
        $this->checkOpen();

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
        $this->checkOpen();

        $isLocked = $orderedTool->isLocked();
        $orderedTool->setLocked(!$isLocked);
        $this->toolManager->editOrderedTool($orderedTool);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/tools/order/update/ordered/tool/{orderedTool}/with/{otherOrderedTool}/mode/{mode}/type/{type}",
     *     name="claro_admin_desktop_update_ordered_tool_order",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param OrderedTool $orderedTool
     * @param OrderedTool $otherOrderedTool
     * @param string $mode
     * @param int type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateOrderedToolOrderAction(
        OrderedTool $orderedTool,
        OrderedTool $otherOrderedTool,
        $mode,
        $type = 0
    )
    {
        $this->checkOpen();

        if (is_null($orderedTool->getUser()) &&
            is_null($otherOrderedTool->getWorkspace()) &&
            $orderedTool->getType() === intval($type) &&
            $otherOrderedTool->getType() === intval($type)) {

            $order = $orderedTool->getOrder();
            $otherOrder = $otherOrderedTool->getOrder();

            if ($mode === 'previous') {

                if ($otherOrder > $order) {
                    $newOrder = $otherOrder;
                } else {
                    $newOrder = $otherOrder + 1;
                }
            } elseif ($mode === 'next') {

                if ($otherOrder > $order) {
                    $newOrder = $otherOrder - 1;
                } else {
                    $newOrder = $otherOrder;
                }
            } else {

                return new Response('Bad Request', 400);
            }

            $this->toolManager->updateOrderedToolOrderForAdmin(
                $orderedTool,
                $newOrder,
                $type
            );

            return new Response('success', 204);
        } else {

            throw new AccessDeniedException();
        }
    }

    private function checkOpen()
    {
        if ($this->securityContext->isGranted('OPEN', $this->desktopAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
