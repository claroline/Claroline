<?php

namespace Claroline\CoreBundle\Manager;

use Composer\Repository\InstalledFilesystemRepository;
use Composer\Json\JsonFile;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.dependency_manager")
 */
class DependencyManager {

    private $vendorDir;
    private $jsonFile;

    const CLAROLINE_CORE_TYPE = 'claroline-core';
    const CLAROLINE_PLUGIN_TYPE = 'claroline-plugin';

    /**
     * @DI\InjectParams({"vendorDir" = @DI\Inject("%claroline.param.vendor_directory%")})
     */
    public function __construct($vendorDir)
    {
        $this->vendorDir = $vendorDir;
        $ds = DIRECTORY_SEPARATOR;

        $this->jsonFile = "{$vendorDir}{$ds}composer{$ds}installed.json";
    }

    public function getAllInstalled()
    {
        $installedFile = new JsonFile($this->jsonFile);
        $repo = new InstalledFilesystemRepository($installedFile);

        return $repo->getPackages();
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getInstalledByType($type)
    {
        $packages = [];

        foreach ($this->getAllInstalled() as $package) {

            if ($package->getType() === $type) {
                $packages[] = $package;
            }
        }

        return $packages;
    }
}