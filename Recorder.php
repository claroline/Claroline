<?php

namespace Claroline\BundleRecorder;

use Composer\Composer;
use Composer\Package\PackageInterface;

class Recorder
{
    private $composer;
    private $bundleFile;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
        $this->bundleFile = $this->getBundleFile();

        if (!file_exists($this->bundleFile)) {
            touch($this->bundleFile);
        }
    }

    public function addBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'add');
    }

    public function removeBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'remove');
    }

    public function detectBundles(PackageInterface $package)
    {
        $path = $this->getPackagePath($package);
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new FilterIterator($iterator);
        $items = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        $bundles = array();

        foreach ($items as $item) {
            if (preg_match('#^(.+Bundle)\.php$#', $item->getBasename(), $matches)) {
                if (false !== strpos(file_get_contents($item->getPathname()), 'abstract class')) {
                    continue;
                }

                $fqcnParts = array($matches[1]);
                $pathParts = array_reverse(explode(DIRECTORY_SEPARATOR, $item->getPath()));

                foreach ($pathParts as $part) {
                    if (ctype_upper($part[0])) {
                        array_unshift($fqcnParts, $part);
                        continue;
                    }

                    break;
                }

                $bundles[] = implode('\\', $fqcnParts);
            }
        }

        return $bundles;
    }

    private function getBundleFile()
    {
        $options = array_merge(
            array('bundle-file' => 'app/config/bundles.ini'),
            $this->composer->getPackage()->getExtra()
        );

        return $options['bundle-file'];
    }

    private function updateBundleFile(array $bundlesFqcns, $action)
    {
        $recordedBundles = parse_ini_file($this->bundleFile);
        $hasChanges = false;

        foreach ($bundlesFqcns as $bundleFqcn) {
            if ($action === 'add' && !isset($recordedBundles[$bundleFqcn])) {
                $recordedBundles[$bundleFqcn] = true;
                $hasChanges = true;
            } elseif ($action === 'remove' && isset($recordedBundles[$bundleFqcn])) {
                unset($recordedBundles[$bundleFqcn]);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $content = '';

            foreach ($recordedBundles as $bundle => $isEnabled) {
                $isEnabled = $isEnabled ? 'true' : 'false';
                $content .= "{$bundle} = {$isEnabled}" . PHP_EOL;
            }

            file_put_contents($this->bundleFile, $content);
        }
    }

    private function getPackagePath(PackageInterface $package)
    {
        $vendorDir = rtrim($this->composer->getConfig()->get('vendor-dir'), '/');

        return realpath(($vendorDir ? $vendorDir . '/' : '') . $package->getPrettyName());
    }
}