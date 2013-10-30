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

    public function refresh($environment = 'dev')
    {
        $output = $this->output ?: new NullOutput();
        $this->installAssets($output);
        $this->dumpAssets($output, $environment);
        $this->clearCache($output);
    }

    public function installAssets(OutputInterface $output)
    {
        $assetInstallInput = new ArrayInput(array('--symlink' => true));
        $assetInstallCmd = new AssetsInstallCommand();
        $assetInstallCmd->setContainer($this->container);
        $assetInstallCmd->run($assetInstallInput, $output);
    }

    public function dumpAssets(OutputInterface $output, $environment)
    {
        $assetDumpCmd = new DumpCommand();
        $assetDumpCmd->setContainer($this->container);
        $assetDumpCmd->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'Env', $environment)
        );
        $assetDumpCmd->run(new ArrayInput(array()), $output);
    }

    public function clearCache(OutputInterface $output)
    {
        $output->writeln('Clearing the cache...');
        $fileSystem = new Filesystem();
        $cacheIterator = new \DirectoryIterator(
            $this->container->get('kernel')->getRootDir() . '/cache'
        );

        foreach ($cacheIterator as $item) {
            if (!$item->isDot() && $item->isDir()) {
                $fileSystem->remove($item->getPathname());
            }
        }
    }
}
