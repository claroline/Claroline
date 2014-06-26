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

use JMS\DiExtraBundle\Annotation as DI;
use Composer\Package\CompletePackageInterface;
use Composer\Repository\Vcs\GitDriver;
use Composer\IO\NullIO;
use Composer\Config;
use Composer\Util\RemoteFilesystem;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Json\JsonFile;

/**
 * @DI\Service("claroline.manager.dependency_manager")
 */
class DependencyManager {

    private $vendorDir;
    private $repo;
    private $jsonFile;
    private $lastTagsFile;
    private $iniFileManager;

    const CLAROLINE_CORE_TYPE = 'claroline-core';
    const CLAROLINE_PLUGIN_TYPE = 'claroline-plugin';

    /**
     * @DI\InjectParams({
     *      "vendorDir"      = @DI\Inject("%claroline.param.vendor_directory%"),
     *      "lastTagsFile"   = @DI\Inject("%claroline.packages_last_tags_file%"),
     *      "iniFileManager" = @DI\Inject("claroline.manager.ini_file_manager")
     * })
     */
    public function __construct($vendorDir, $lastTagsFile, $iniFileManager)
    {
        $this->vendorDir = $vendorDir;
        $ds = DIRECTORY_SEPARATOR;
        $this->jsonFile = "{$vendorDir}{$ds}composer{$ds}installed.json";
        $installedFile = new JsonFile($this->jsonFile);
        $this->repo = new InstalledFilesystemRepository($installedFile);
        $this->lastTagsFile = $lastTagsFile;
        $this->iniFileManager = $iniFileManager;
    }

    /**
     * Returns every packages installed by composer.
     *
     * @return CompletePackageInterface[]
     */
    public function getAllInstalled()
    {
        return $this->repo->getPackages();
    }

    /**
     * Returns installed packages by composer by type.
     *
     * @param $type
     *
     * @return CompletePackageInterface[]
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

    /**
     * Finds package by dist reference.
     *
     * @param string $distReference
     *
     * @return CompletePackageInterface|null
     */
    public function getByDistReference($distReference)
    {
        foreach ($this->getAllInstalled() as $package) {

            if ($package->getDistReference() === $distReference) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Return each installed claroline plugins or core packages.
     *
     * @return array
     */
    public function getAllClaroPackages()
    {
        return array_merge(
            $this->getInstalledByType(self::CLAROLINE_CORE_TYPE),
            $this->getInstalledByType(self::CLAROLINE_PLUGIN_TYPE)
        );
    }

    /**
     * Returns a package tag list.
     *
     * @param CompletePackageInterface $package
     *
     * @return array
     */
    public function getPackageTags(CompletePackageInterface $package)
    {
        $config = new Config();
        $config->merge(array('config' => array('home' => sys_get_temp_dir() . '/' . uniqid())));
        $io = new NullIO();

        $driver = new GitDriver(
            array('url' => $package->getSourceUrl()),
            $io,
            $config,
            null,
            new RemoteFilesystem($io)
        );
        $driver->initialize();

        return $driver->getTags();
    }

    /**
     * Returns the last tag of a package.
     *
     * @param CompletePackageInterface $package
     *
     * @return string
     */
    public function getLastTag(CompletePackageInterface $package)
    {
        $tags = $this->getPackageTags($package);
        $lastTag = 0;
        $toReturn = 0;

        foreach ($tags as $tag => $commit) {

            //remove the "v" from the version tags. It's hard to compare otherwise.
            $tag = str_replace('v', '', $tag);

            if (version_compare($lastTag, $tag, '<')) {
                $lastTag = $tag;
            }
        }

        return $lastTag;
    }

    /**
     * Update the cache file containing the list of last available tags.
     *
     * @return array
     */
    public function updateAllPackages()
    {
        $packages = $this->getAllClaroPackages();

        foreach ($packages as $package) {
            $this->updatePackage($package);
        }

        return $tagList;
    }

    /**
     * Update a single package in the cache file.
     *
     * @param CompletePackageInterface $package
     */
    public function updatePackage(CompletePackageInterface $package)
    {
        $tag = $this->getLastTag($package);
        $this->iniFileManager->updateKey($package->getPrettyName(), $tag, $this->lastTagsFile);

        return $tag;
    }

    /**
     * Returns if the tag cache exists.
     *
     * @return bool
     */
    public function cacheExists()
    {
        return file_exists($this->lastTagsFile);
    }

    /**
     * Returns the tag cache.
     */
    private function removeCache()
    {
        if ($this->cacheExists()) {
            unlink($this->lastTagsFile);
        }
    }

    /**
     * Returns a list of upgradeable packages.
     *
     * @return array
     */
    public function getUpgradeablePackages()
    {
        //if the cache does not exists, wait for a refresh
        if (!$this->cacheExists()) {
            return array();
        }

        $tags = parse_ini_file($this->lastTagsFile);
        $packages = $this->getAllClaroPackages();
        $toUpgrade = [];

        foreach ($packages as $package) {
            foreach ($tags as $prettyName => $tag) {
                if ($package->getPrettyName() === $prettyName) {
                    $toUpgrade[] = array('tag' => $tag, 'package' => $package);
                }
            }
        }

        return $toUpgrade;
    }
}