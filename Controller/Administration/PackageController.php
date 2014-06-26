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

    /**
     * @DI\InjectParams({
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *      "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *      "dm"              = @DI\Inject("claroline.manager.dependency_manager"),
     *      "sc"              = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        StrictDispatcher         $eventDispatcher,
        ToolManager              $toolManager,
        SecurityContextInterface $sc,
        DependencyManager        $dm
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->toolManager     = $toolManager;
        $this->adminToolPlugin = $toolManager->getAdminToolByName('platform_packages');
        $this->sc              = $sc;
        $this->dm              = $dm;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_plugins"
     * )
     * @EXT\Method("GET")
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
        $pluginPackages = $this->dm->getInstalledByType(DependencyManager::CLAROLINE_PLUGIN_TYPE);
        $upgradablePackages = $this->dm->getUpgradeablePackages();

        return array(
            'corePackages' => $corePackages,
            'pluginPackages' => $pluginPackages,
            'upgradablePackages' => $upgradablePackages
        );
    }

    /**
     * @EXT\Route(
     *     "/update/all",
     *     name="claro_admin_update_packages",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function updateAllAction()
    {
        $this->checkOpen();
        $packages = $this->dm->updateLastTagCache();

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/update/package/{ref}",
     *     name="claro_admin_update_package",
     *     options={"expose"=true}
     * )
     * @param $package
     */
    public function updatePackageAction($ref)
    {
        $this->checkOpen();
        $package = $this->dm->getByDistReference($ref);
        $tag = $this->dm->updatePackage($package);

        return new JsonResponse(
            array(
                'tag' => $tag,
                'prettyName' => $package->getPrettyName(),
                'distRef' => $package->getDistReference()
            )
        );
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->adminToolPlugin)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}