<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Update\Version;
use Claroline\CoreBundle\Library\PluginBundleInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Composer\Json\JsonFile;
use Composer\Repository\InstalledFilesystemRepository;
use FOS\RestBundle\View\View;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @DI\Service("claroline.manager.version_manager")
 */
class VersionManager
{
    use LoggableTrait;

    /**
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $container
    ) {
        $this->om = $om;
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Update\Version');
        $this->container = $container;
        $this->installedRepoFile = $this->container->get('kernel')->getRootDir().'/../vendor/composer/installed.json';
    }

    public function register(InstallableInterface $bundle)
    {
        $data = $this->getVersionFile($bundle);

        $version = $this->repo->findOneBy(['version' => $data[0], 'bundle' => $bundle->getBundleFQCN()]);

        if ($version) {
            $this->log("Version {$version->getBundle()} {$version->getVersion()} already registered !", LogLevel::ERROR);

            return $version;
        }

        $this->log("Registering {$bundle->getBundleFQCN()} version {$data[0]}");
        $version = new Version($data[0], $data[1], $data[2], $bundle->getBundleFQCN());
        $this->om->persist($version);
        $this->om->flush();

        return $version;
    }

    public function execute(Version $version)
    {
        $version->setIsUpgraded(true);
        $this->om->persist($version);
        $this->om->flush();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getCurrent()
    {
        return $this->getVersionFile()[0];
    }

    public function getLatestUpgraded($bundle)
    {
        $fqcn = $bundle instanceof PluginBundleInterface ? $bundle->getBundleFQCN() : $bundle;

        try {
            return $this->repo->getLatestExecuted($fqcn);
        } catch (\Exception $e) {
            //table is not here yet if version < 10
            return null;
        }
    }

    public function getVersionFile()
    {
        $data = file_get_contents($this->getDistributionVersionFilePAth());

        return explode("\n", $data);
    }

    public function getDistributionVersion()
    {
        return $this->getVersionFile()[0];
    }

    public function getDistributionVersionFilePAth()
    {
        return __DIR__.'/../../../VERSION.txt';
    }

    /**
     * @param string $repoFile
     * @param bool   $filter
     *
     * @return InstalledFilesystemRepository
     */
    public function openRepository($repoFile, $filter = true)
    {
        $json = new JsonFile($repoFile);

        if (!$json->exists()) {
            throw new \RuntimeException(
               "'{$repoFile}' must be writable",
               456 // this code is there for unit testing only
            );
        }

        $repo = new InstalledFilesystemRepository($json);

        if ($filter) {
            foreach ($repo->getPackages() as $package) {
                if ($package->getType() !== 'claroline-core'
                    && $package->getType() !== 'claroline-plugin') {
                    $repo->removePackage($package);
                }
            }
        }

        return $repo;
    }

    public function findInstalledPackage(PluginBundleInterface $bundle)
    {
        //look for something in the database
        $package = $this->getLatestUpgraded($bundle);

        if ($package) {
            return $package;
        }

        $previous = $this->versionManager->openRepository($this->previousRepoFile, true);

        if (!$previous) {
            return;
        }

        foreach ($previous->getCanonicalPackages() as $package) {
            $extra = $package->getExtra();

            if ($extra && array_key_exists('bundles', $extra)) {
                //Otherwise convert the name in a dirty little way
              //If it's a metapackage, check in the bundle list
              foreach ($extra['bundles'] as $installedBundle) {
                  if ($installedBundle === $bundle) {
                      return new Package($package->getName(), $package->getVersion());
                  }
              }
            } else {
                $bundleParts = explode('\\', $bundle);

              //magic !
              if (preg_replace('/[^A-Za-z0-9]/', '', $package->getPrettyName()) === strtolower($bundleParts[2])) {
                  return new Package($package->getName(), $package->getVersion());
              }
            }
        }
    }
}
