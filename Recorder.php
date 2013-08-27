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
    }

    public function addBundlesFrom(PackageInterface $package)
    {
        $this->updateBundleFile($package, 'add');
    }

    public function removeBundlesFrom(PackageInterface $package)
    {
        $this->updateBundleFile($package, 'remove');
    }

    private function getBundleFile()
    {
        $options = array_merge(
            array('bundle-file' => 'app/config/bundles.ini'),
            $this->composer->getPackage()->getExtra()
        );

        return $options['bundle-file'];
    }

    private function updateBundleFile(PackageInterface $package, $action)
    {
        $path = $this->getPackagePath($package);
        $bundles = $this->detectBundles($path);
        $recordedBundles = parse_ini_file($this->bundleFile);
        $hasChanges = false;

        foreach ($bundles as $bundleFqcn) {
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

    private function detectBundles($path)
    {
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new FilterIterator($iterator);
        $items = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        $bundles = array();

        foreach ($items as $item) {
            if (preg_match('#^(.+Bundle)\.php$#', $item->getBasename(), $matches)) {
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
}