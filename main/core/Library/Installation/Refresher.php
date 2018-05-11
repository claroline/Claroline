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
use Composer\Script\Event;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.installation.refresher")
 */
class Refresher
{
    /** @var ContainerInterface */
    private $container;
    /** @var OutputInterface */
    private $output;

    /**
     * Refresher constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
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

        if ($clearCache) {
            $this->clearCache($environment);
        }
    }

    public function installAssets()
    {
        $webDir = "{$this->container->get('kernel')->getRootDir()}/../web";
        $args = ['command' => 'assets:install', 'target' => $webDir, '--symlink' => true];
        $this->container->get('claroline.manager.command_manager')
          ->run(new ArrayInput($args), $this->output ?: new NullOutput());
    }

    public function dumpAssets($environment)
    {
        if ($this->output) {
            $this->output->writeln('Dumping translations...');
        }

        $cmdManager = $this->container->get('claroline.manager.command_manager');
        $cmdManager->run(new ArrayInput(['command' => 'bazinga:js-translation:dump']), $this->output ?: new NullOutput());

        if ($this->output) {
            $this->output->writeln('Compiling javascripts...');
        }

        $cmdManager->run(new ArrayInput(['command' => 'assetic:dump', '--env' => $environment]), $this->output ?: new NullOutput());
    }

    public function buildThemes()
    {
        $this->container->get('claroline.manager.command_manager')
          ->run(new ArrayInput(['command' => 'claroline:theme:build']), $this->output);
    }

    public function clearCache($environment = null)
    {
        if ($this->output) {
            $this->output->writeln('Clearing the cache...');
        }

        $baseCacheDir = "{$this->container->get('kernel')->getRootDir()}/cache";
        $cacheDir = null === $environment ? $baseCacheDir : "{$baseCacheDir}/{$environment}";
        static::removeContentFrom($cacheDir);
    }

    public static function deleteCache(Event $event)
    {
        $options = array_merge(
            ['symfony-app-dir' => 'app'],
            $event->getComposer()->getPackage()->getExtra()
        );

        $cacheDir = $options['symfony-app-dir'].'/cache';
        $event->getIO()->write('Clearing the cache...');
        static::removeContentFrom($cacheDir);
    }

    public static function removeContentFrom($directory)
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
}
