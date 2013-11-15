<?php

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

    public function refresh($environment)
    {
        $output = $this->output ?: new NullOutput();
        $this->installAssets($output);
        $this->dumpAssets($environment, $output);
        $this->clearCache($environment, $output);
    }

    public function installAssets(OutputInterface $output = null)
    {
        $webDir = "{$this->container->get('kernel')->getRootDir()}/../web";
        $assetInstallInput = new ArrayInput(
            array('target' => $webDir, '--symlink' => true)
        );
        $assetInstallCmd = new AssetsInstallCommand();
        $assetInstallCmd->setContainer($this->container);
        $assetInstallCmd->run($assetInstallInput, $output ?: new NullOutput());
    }

    public function dumpAssets($environment, OutputInterface $output = null)
    {
        $assetDumpCmd = new DumpCommand();
        $assetDumpCmd->setContainer($this->container);
        $assetDumpCmd->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'Env', $environment)
        );
        $assetDumpCmd->run(new ArrayInput(array()), $output ?: new NullOutput());
    }

    public function clearCache($environment = null, OutputInterface $output = null)
    {
        if ($output) {
            $output->writeln('Clearing the cache...');
        }

        $fileSystem = new Filesystem();
        $baseCacheDir = "{$this->container->get('kernel')->getRootDir()}/cache";
        $cacheDir = $environment === null ? $baseCacheDir : "{$baseCacheDir}/{$environment}";
        $cacheIterator = new \DirectoryIterator($cacheDir);

        foreach ($cacheIterator as $item) {
            if (!$item->isDot()) {
                $fileSystem->remove($item->getPathname());
            }
        }
    }
}
