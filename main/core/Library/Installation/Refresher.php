<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Refresher
{
    /** @var ContainerInterface */
    private $container;
    /** @var OutputInterface */
    private $output;

    /**
     * Refresher constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function refresh($environment, $clearCache = true)
    {
        $this->buildSymlinks();

        $this->installAssets();
        $this->dumpAssets($environment);

        if ($clearCache) {
            $this->clearCache($environment);
        }
    }

    public function installAssets()
    {
        $webDir = $this->container->getParameter('claroline.param.public_directory');
        $args = ['command' => 'assets:install', 'target' => $webDir, '--symlink' => true];
        $this->container->get('claroline.manager.command_manager')
          ->run(new ArrayInput($args), $this->output ?: new NullOutput());
    }

    public function dumpAssets($environment)
    {
        if ($this->output) {
            $this->output->writeln('Dumping translations...');
        }

        $translationTargetDir = $this->container->getParameter('claroline.param.public_directory').'/js';

        $cmdManager = $this->container->get('claroline.manager.command_manager');
        $cmdManager->run(new ArrayInput(['command' => 'bazinga:js-translation:dump', 'target' => $translationTargetDir]), $this->output ?: new NullOutput());

        if ($this->output) {
            $this->output->writeln('Compiling javascripts...');
        }
    }

    public function buildThemes()
    {
        $this->container->get('claroline.manager.command_manager')
          ->run(new ArrayInput(['command' => 'claroline:theme:build']), $this->output);
    }

    public function buildSymlinks()
    {
        $this->linkPublicFiles();
        $this->linkPackageFiles();
    }

    public function clearCache($environment = null)
    {
        if ($this->output) {
            $this->output->writeln('Clearing the cache...');
        }

        $baseCacheDir = "{$this->container->get('kernel')->getProjectDir()}/var/cache";
        $cacheDir = null === $environment ? $baseCacheDir : "{$baseCacheDir}/{$environment}";
        static::removeContentFrom($cacheDir);
    }

    private static function removeContentFrom($directory)
    {
        if (is_dir($directory)) {
            $fileSystem = new Filesystem();
            $cacheIterator = new \DirectoryIterator($directory);

            foreach ($cacheIterator as $item) {
                if (!$item->isDot() && '.gitkeep' !== $item->getFilename()) {
                    $fileSystem->remove($item->getPathname());
                }
            }
        }
    }

    private function linkPublicFiles()
    {
        // create symlink for public files
        $dataWebDir = $this->container->getParameter('claroline.param.data_web_dir');
        $fileSystem = $this->container->get('filesystem');
        $publicFilesDir = $this->container->getParameter('claroline.param.public_files_directory');

        if (!$fileSystem->exists($dataWebDir)) {
            $this->output->writeln('Creating symlink to public directory of files directory in public directory...');
            $fileSystem->symlink($publicFilesDir, $dataWebDir);
        } else {
            if (!is_link($dataWebDir)) {
                //we could remove it manually but it might be risky
                $this->output->writeln('Symlink from public/data to files/data could not be created, please remove your public/data folder manually', LogLevel::ERROR);
            } else {
                $this->output->writeln('Public folder symlinks validated...');
            }
        }
    }

    private function linkPackageFiles()
    {
        $fileSystem = $this->container->get('filesystem');
        $webDir = $this->container->getParameter('claroline.param.public_directory');

        if (!$fileSystem->exists($webDir.'/packages')) {
            $this->output->writeln('Creating symlink to '.$webDir.'/packages');
            $fileSystem->symlink($webDir.'/../node_modules', $webDir.'/packages');
        } elseif (!is_link($webDir.'/packages')) {
            $this->output->writeln('Couldn\'t create symlink to from node_modules to public/packages. You must remove public/packages or create the link manually');
        }
    }
}
