<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder\Detector;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

class Detector
{
    use LoggableTrait;
    private $baseDir;

    public function __construct($baseDir = null)
    {
        $this->baseDir = $baseDir;
    }

    public function detectBundles($path)
    {
        $path = $this->baseDir ? "{$this->baseDir}/{$path}" : $path;

        if (!is_dir($path)) {
            return [];
        }

        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new FilterIterator($iterator);
        $items = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        $bundles = [];

        //look for a bundle list in the composer.json for meta packages
        if (file_exists($path.'/composer.json')) {
            $json = json_decode(file_get_contents($path.'/composer.json'), true);

            if (array_key_exists('extra', $json) && array_key_exists('bundles', $json['extra'])) {
                foreach ($json['extra']['bundles'] as $bundle) {
                    $bundles[] = $bundle;
                }

                return $bundles;
            }
        }

        foreach ($items as $item) {
            if (preg_match('#^(.+Bundle)\.php$#', $item->getBasename())) {
                if ($bundle = $this->findBundleClass($item->getPathname())) {
                    $bundles[] = $bundle;
                }
            }
        }

        return $bundles;
    }

    public function detectBundle($path)
    {
        $bundles = $this->detectBundles($path);

        if (1 !== $count = count($bundles)) {
            $msg = "Expected one bundle in class {$path}, {$count} found";
            $msg .= $count === 0 ? '.' : ('('.implode(', ', $bundles).').');

            throw new \Exception($msg);
        }

        return $bundles[0];
    }

    private function findBundleClass($file)
    {
        $content = file_get_contents($file);

        // exclude abstract base classes
        if (preg_match('#abstract\s+class#i', $content)) {
            return;
        }

        // extract the class name with namespace using tokenization
        // (see http://stackoverflow.com/a/7153391)

        $tokens = token_get_all($content);
        $namespaceSegments = [];
        $class = null;

        foreach (array_keys($tokens) as $i) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); ++$j) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespaceSegments[] = $tokens[$j][1];
                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); ++$j) {
                    if ($tokens[$j] === '{') {
                        $class = $tokens[$i + 2][1];
                    }
                }
            }
        }

        if ($class) {
            $namespaceSegments[] = $class;
            $fqcn = implode('\\', $namespaceSegments);
            $bundleInterface = 'Symfony\Component\HttpKernel\Bundle\BundleInterface';

            try {
                if (in_array($bundleInterface, class_implements($fqcn))) {
                    return $fqcn;
                }
            } catch (\Exception $e) {
                $this->log("Class {$fqcn} was not loaded");

                return false;
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
