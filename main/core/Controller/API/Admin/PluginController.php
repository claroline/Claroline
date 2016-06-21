<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Admin;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Manager\PluginManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Patch;
use Claroline\CoreBundle\Entity\Plugin;

/**
 * @NamePrefix("api_")
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class PluginController extends FOSRestController
{
    private $request;
    private $bundleManager;

    /**
     * @DI\InjectParams({
     *     "request"       = @DI\Inject("request"),
     *	   "bundleManager" = @DI\Inject("claroline.manager.plugin_manager")
     * })
     */
    public function __construct(Request $request, PluginManager $bundleManager)
    {
        $this->request = $request;
        $this->bundleManager = $bundleManager;
    }

    /**
     * @ApiDoc(
     *     description="Returns the plugin list",
     *     views = {"plugin"}
     * )
     */
    public function getPluginsAction()
    {
        return $this->bundleManager->getPluginsData();
    }

    /**
     * @View(serializerGroups={"api_plugin"})
     * @ApiDoc(
     *     description="Returns the plugin list",
     *     views = {"plugin"}
     * )
     * @Patch("/plugin/{plugin}/enable")
     */
    public function enablePluginAction(Plugin $plugin)
    {
        $this->bundleManager->enable($plugin);

        return $this->bundleManager->getPluginsData();
    }

    /**
     * @View(serializerGroups={"api_plugin"})
     * @ApiDoc(
     *     description="Returns the plugin list",
     *     views = {"plugin"}
     * )
     * @Patch("/plugin/{plugin}/disable")
     */
    public function disablePluginAction(Plugin $plugin)
    {
        $this->bundleManager->disable($plugin);

        return $this->bundleManager->getPluginsData();
    }
}
