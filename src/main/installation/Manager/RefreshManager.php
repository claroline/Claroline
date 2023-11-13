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

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class RefreshManager
{
    private OutputInterface $output;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
        private readonly string $cacheDir,
        private readonly string $publicDir,
        private readonly string $publicDataDir,
        private readonly string $filesDataDir
    ) {
        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function refresh(string $environment): void
    {
        $this->buildSymlinks();
        $this->installAssets();
        $this->dumpAssets();
        $this->clearCache($environment);
    }

    public function installAssets(): void
    {
        $this->runCommand(new ArrayInput([
            'command' => 'assets:install',
            'target' => $this->publicDir,
            '--symlink' => true,
        ]), $this->output);
    }

    public function dumpAssets(): void
    {
        $this->runCommand(new ArrayInput([
            'command' => 'bazinga:js-translation:dump',
            'target' => $this->publicDir.DIRECTORY_SEPARATOR.'js',
            '--format' => ['js'],
            '--merge-domains' => true,
        ]), $this->output);
    }

    public function buildThemes(): void
    {
        $this->runCommand(new ArrayInput([
            'command' => 'claroline:theme:build',
        ]), $this->output);
    }

    public function buildSymlinks(): void
    {
        $this->linkPublicFiles();
        $this->linkPackageFiles();
    }

    public function clearCache(string $environment): void
    {
        $this->output->writeln('Clearing the cache...');

        $this->removeContentFrom($this->cacheDir.DIRECTORY_SEPARATOR.$environment);
    }

    private function removeContentFrom($directory): void
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

    private function linkPublicFiles(): void
    {
        if (!$this->filesystem->exists($this->publicDataDir)) {
            $this->output->writeln('Creating symlink to public directory of files directory in public directory...');
            $this->filesystem->symlink($this->filesDataDir, $this->publicDataDir);
        } else {
            if (!is_link($this->publicDataDir)) {
                // we could remove it manually but it might be risky
                $this->output->writeln('Symlink from public/data to files/data could not be created, please remove your public/data folder manually', LogLevel::ERROR);
            } else {
                $this->output->writeln('Public folder symlinks validated...');
            }
        }
    }

    private function linkPackageFiles(): void
    {
        $packageDir = $this->publicDir.DIRECTORY_SEPARATOR.'packages';

        if (!$this->filesystem->exists($packageDir)) {
            $this->output->writeln('Creating symlink to '.$packageDir);
            $this->filesystem->symlink($this->projectDir.DIRECTORY_SEPARATOR.'node_modules', $packageDir);
        } elseif (!is_link($packageDir)) {
            $this->output->writeln('Cannot create symlink from node_modules to public/packages. You must remove public/packages or create the link manually');
        }
    }

    private function runCommand(ArrayInput $input, $output): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}
