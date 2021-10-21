<?php

namespace Claroline\KernelBundle;

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Manager\BundleManager;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private $bundlesFile;
    /** @var BundleManager */
    private $bundleManager;

    public function __construct(string $environment, bool $debug)
    {
        // make sure dates are in UTC regardless to the server config
        date_default_timezone_set('UTC');

        $this->bundlesFile = $this->getProjectDir().'/files/config/bundles.ini';

        BundleManager::initialize($environment, $this->bundlesFile);
        $this->bundleManager = BundleManager::getInstance();

        parent::__construct($environment, $debug);
    }

    public function getProjectDir(): string
    {
        return realpath(__DIR__.'/../../..');
    }

    public function registerBundles(): iterable
    {
        // MaintenanceHandler::isMaintenanceEnabled() is a hacky way to know we are in update/install
        // command and we need to enable all plugins in order to update them
        // We also need all plugins in tests environment to be able to run their tests suite
        $fetchAll = MaintenanceHandler::isMaintenanceEnabled() || 'test' === $this->environment;

        return $this->bundleManager->getActiveBundles($fetchAll);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/parameters.yml');

        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AutoConfigurableInterface) {
                $bundle->configureContainer($container, $loader);
            }
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AutoConfigurableInterface) {
                $bundle->configureRoutes($routes);
            }
        }
    }
}
