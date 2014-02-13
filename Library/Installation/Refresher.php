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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Bundle\AsseticBundle\Command\DumpCommand;
use Symfony\Component\Filesystem\Filesystem;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.installation.refresher")
 */
class Refresher
{
    private $container;
    private $output;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
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
        $this->installAssets();
        $this->dumpAssets($environment);
        $this->compileGeneratedThemes();

        if ($clearCache) {
            $this->clearCache($environment);
        }
    }

    public function installAssets()
    {
        $webDir = "{$this->container->get('kernel')->getRootDir()}/../web";
        $args = array('target' => $webDir);

        if (function_exists('symlink')) {
            $args['--symlink'] = true;
        }

        $assetInstallCmd = new AssetsInstallCommand();
        $assetInstallCmd->setContainer($this->container);
        $assetInstallCmd->run(new ArrayInput($args), $this->output ?: new NullOutput());
    }

    public function dumpAssets($environment)
    {
        $assetDumpCmd = new DumpCommand();
        $assetDumpCmd->setContainer($this->container);
        $assetDumpCmd->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'Env', $environment)
        );
        $assetDumpCmd->run(new ArrayInput(array()), $this->output ?: new NullOutput());
    }

    public function clearCache($environment = null)
    {
        if ($this->output) {
            $this->output->writeln('Clearing the cache...');
        }

        $fileSystem = new Filesystem();
        $baseCacheDir = "{$this->container->get('kernel')->getRootDir()}/cache";
        $cacheDir = $environment === null ? $baseCacheDir : "{$baseCacheDir}/{$environment}";

        if (!is_dir($cacheDir)) {
            return;
        }

        $cacheIterator = new \DirectoryIterator($cacheDir);

        foreach ($cacheIterator as $item) {
            if (!$item->isDot()) {
                $fileSystem->remove($item->getPathname());
            }
        }
    }

    public function compileGeneratedThemes()
    {
        if ($this->output) {
            $this->output->writeln('Re-compiling generated themes...');
        }

        $themeService = $this->container->get('claroline.common.theme_service');

        foreach ($themeService->getThemes('less-generated') as $theme) {
            if ($this->output) {
                $this->output->writeln("    Compiling '{$theme->getName()}' theme...");
            }

            $themeService->compileRaw(array($theme->getName()));
        }
    }
}
