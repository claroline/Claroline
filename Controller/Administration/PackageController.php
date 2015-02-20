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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\DependencyManager;
use Claroline\CoreBundle\Manager\BundleManager;
use Claroline\CoreBundle\Manager\IPWhiteListManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class PackageController extends Controller
{
    private $toolManager;
    private $eventDispatcher;
    private $adminToolPlugin;
    private $sc;
    private $ipwlm;
    private $bundleManager;

    /**
     * @DI\InjectParams({
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *      "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *      "dm"              = @DI\Inject("claroline.manager.dependency_manager"),
     *      "sc"              = @DI\Inject("security.context"),
     *      "ipwlm"           = @DI\Inject("claroline.manager.ip_white_list_manager"),
     *      "bundleManager"   = @DI\Inject("claroline.manager.bundle_manager")
     * })
     */
    public function __construct(
        StrictDispatcher         $eventDispatcher,
        ToolManager              $toolManager,
        SecurityContextInterface $sc,
        DependencyManager        $dm,
        IPWhiteListManager       $ipwlm,
        BundleManager            $bundleManager
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->toolManager     = $toolManager;
        $this->adminToolPlugin = $toolManager->getAdminToolByName('platform_packages');
        $this->sc              = $sc;
        $this->dm              = $dm;
        $this->ipwlm           = $ipwlm;
        $this->bundleManager   = $bundleManager;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_plugins"
     * )
     *
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function listAction()
    {
        $this->checkOpen();
        $corePackages = $this->dm->getInstalledByType(DependencyManager::CLAROLINE_CORE_TYPE);
        $pluginPackages = $this->dm->getPluginList();
        $upgradablePackages = $this->dm->getUpgradeablePackages();
        $ds = DIRECTORY_SEPARATOR;

        //the current ip must be whitelisted so it can access the upgrade.html.php script
        $this->ipwlm->addIP($_SERVER['REMOTE_ADDR']);
        $allowUpdate = true;

        return array(
            'corePackages'       => $corePackages,
            'pluginPackages'     => $pluginPackages,
            'upgradablePackages' => $upgradablePackages,
            'allowUpdate'        => $allowUpdate
        );
    }

    /**
     * @EXT\Route(
     *     "/packages/available",
     *     name="claro_admin_available_packages"
     * )
     *
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function availablePackagesAction()
    {
        $this->checkOpen();
        $coreBundle = $this->bundleManager->getBundle($coreBundle);
        $coreVersion = $coreBundle->getVersion();

        //ask the server wich are the last available packages now.

    }

    /**
     * @EXT\Route(
     *     "/plugin/parameters/{pluginShortName}",
     *     name="claro_admin_plugin_parameters"
     * )
     */
    public function pluginParametersAction($pluginShortName)
    {
        $this->checkOpen();
        $eventName = "plugin_options_{$pluginShortName}";
        $event = $this->eventDispatcher->dispatch($eventName, 'PluginOptions', array());

        return $event->getResponse();
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->adminToolPlugin)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
