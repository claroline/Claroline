<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Manager;

use Claroline\AppBundle\Manager\CommandManager;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RefreshManager
{
    /** @var Filesystem */
    private $filesystem;
    /** @var CommandManager */
    private $commandManager;

    /** @var string */
    private $projectDir;
    /** @var string */
    private $cacheDir;
    /** @var string */
    private $publicDir;
    /** @var string */
    private $publicDataDir;
    /** @var string */
    private $filesDataDir;

    /** @var OutputInterface */
    private $output;

    public function __construct(
        Filesystem $filesystem,
        CommandManager $commandManager,
        string $projectDir,
        string $cacheDir,
        string $publicDir,
        string $publicDataDir,
        string $filesDataDir
    ) {
        $this->filesystem = $filesystem;
        $this->commandManager = $commandManager;

        $this->projectDir = $projectDir;
        $this->cacheDir = $cacheDir;
        $this->publicDir = $publicDir;
        $this->publicDataDir = $publicDataDir;
        $this->filesDataDir = $filesDataDir;

        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function refresh($environment)
    {
        $this->buildSymlinks();
        $this->installAssets();
        $this->dumpAssets();
        $this->clearCache($environment);
    }

    public function installAssets()
    {
        $this->commandManager->run(new ArrayInput([
            'command' => 'assets:install',
            'target' => $this->publicDir,
            '--symlink' => true,
        ]), $this->output);
    }

    public function dumpAssets()
    {
        $this->commandManager->run(new ArrayInput([
            'command' => 'bazinga:js-translation:dump',
            'target' => $this->publicDir.DIRECTORY_SEPARATOR.'js',
        ]), $this->output);
    }

    public function buildThemes()
    {
        $this->commandManager->run(new ArrayInput([
            'command' => 'claroline:theme:build',
        ]), $this->output);
    }

    public function buildSymlinks()
    {
        $this->linkPublicFiles();
        $this->linkPackageFiles();
    }

    public function clearCache(string $environment)
    {
        if ($this->output) {
            $this->output->writeln('Clearing the cache...');
        }

        $this->removeContentFrom($this->cacheDir.DIRECTORY_SEPARATOR.$environment);
    }

    private function removeContentFrom($directory)
    {
        if (is_dir($directory)) {
            $cacheIterator = new \DirectoryIterator($directory);

            foreach ($cacheIterator as $item) {
                if (!$item->isDot() && '.gitkeep' !== $item->getFilename()) {
                    $this->filesystem->remove($item->getPathname());
                }
            }
        }
    }

    private function linkPublicFiles()
    {
        if (!$this->filesystem->exists($this->publicDataDir)) {
            $this->output->writeln('Creating symlink to public directory of files directory in public directory...');
            $this->filesystem->symlink($this->filesDataDir, $this->publicDataDir);
        } else {
            if (!is_link($this->publicDataDir)) {
                //we could remove it manually but it might be risky
                $this->output->writeln('Symlink from public/data to files/data could not be created, please remove your public/data folder manually', LogLevel::ERROR);
            } else {
                $this->output->writeln('Public folder symlinks validated...');
            }
        }
    }

    private function linkPackageFiles()
    {
        $packageDir = $this->publicDir.DIRECTORY_SEPARATOR.'packages';

        if (!$this->filesystem->exists($packageDir)) {
            $this->output->writeln('Creating symlink to '.$packageDir);
            $this->filesystem->symlink($this->projectDir.DIRECTORY_SEPARATOR.'node_modules', $packageDir);
        } elseif (!is_link($packageDir)) {
            $this->output->writeln('Couldn\'t create symlink from node_modules to public/packages. You must remove public/packages or create the link manually');
        }
    }
}
