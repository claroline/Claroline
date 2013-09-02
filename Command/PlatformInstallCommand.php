<?php

namespace Claroline\CoreBundle\Command;

use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Installs the platform, optionaly with plugins and data fixtures.
 */
class PlatformInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:install')
            ->setDescription('Installs the platform according to the config.');
        $this->addOption(
            'with-optional-fixtures',
            'wof',
            InputOption::VALUE_NONE,
            'When set to true, optional data fixtures will be loaded'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getContainer()->get('kernel');
        $baseInstaller = $this->getContainer()->get('claroline.installation.manager');
        $self = $kernel->getBundle('ClarolineCoreBundle');
        $baseInstaller->install($self, !$input->getOption('with-optional-fixtures'));

        // install plugins...

        /*
        $assetCommand = $this->getApplication()->find('assets:install');
        $assetInput = new ArrayInput(
            array(
                'command' => 'assets:install',
                'target' => realpath(__DIR__ . '/../../../../../web'),
                '--symlink' => true
            )
        );
        $assetCommand->run($assetInput, $output);

        $asseticCommand = $this->getApplication()->find('assetic:dump');
        $asseticInput = new ArrayInput(array('command' => 'assetic:dump'));
        $asseticCommand->run($asseticInput, $output);
        */
    }
}
