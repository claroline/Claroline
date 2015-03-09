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

use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DesktopConfigurationController extends Controller
{
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
}
