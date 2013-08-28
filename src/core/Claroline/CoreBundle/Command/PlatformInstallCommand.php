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
            'with-plugins',
            'wp',
            InputOption::VALUE_NONE,
            'When set to true, available plugins will be installed'
        );
        $this->addOption(
            'with-fixtures',
            'wf',
            InputOption::VALUE_NONE,
            'When set to true, data fixtures will be loaded'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $translator = $this->getContainer()->get('translator');
        $translator->setLocale(
            $this->getContainer()->get('claroline.config.platform_config_handler')->getParameter('locale_language')
        );
        $kernel = $this->getApplication()->getKernel();
        $environment = $kernel->getEnvironment();
        $fileSystem = new Filesystem();
        $output->writeln("Generating default {$environment} template...");
        $templateDirectory = $this->getContainer()->getParameter('claroline.param.templates_directory');
        $defaultPath = "{$templateDirectory}default.zip";
        TemplateBuilder::buildDefault($defaultPath, $translator);

        if ($environment === 'test') {
            // save a copy of the original default config
            $configPath = "{$templateDirectory}config.yml";
            file_put_contents($configPath, Yaml::dump(TemplateBuilder::getDefaultConfig($translator), 10));
            // create a test template with additional resources
            $complexArchive = "{$templateDirectory}complex.zip";
            $fileSystem->copy("{$templateDirectory}default.zip", $complexArchive);
            $archive = new \ZipArchive();
            $archive->open($complexArchive);
            $tmpFile = tempnam(sys_get_temp_dir(), 'tmp');
            $templateBuilder = new TemplateBuilder($archive, TemplateBuilder::getDefaultConfig($translator));
            $templateBuilder->addFile($tmpFile, 'empty', 'empty.txt', 1, 2)
                ->addDirectory('main dir', 3)
                ->addFile($tmpFile, 'empty2', 'empty2.txt', 3, 4)
                ->write();
        }

        $output->writeln('Installing the platform...');
        $manager = $this->getContainer()->get('claroline.install.core_installer');
        $manager->install();
        $aclCommand = $this->getApplication()->find('init:acl');
        $aclCommand->run(new ArrayInput(array('command' => 'init:acl')), $output);

        if ($input->getOption('with-fixtures') && ($environment === 'prod' || $environment === 'dev')) {
            $fixturesPath = "{$kernel->getRootDir()}/../src/core/Claroline/CoreBundle/DataFixtures/Required";
            $output->writeln("Loading {$environment} fixtures...");
            $fixtureCommand = $this->getApplication()->find('doctrine:fixtures:load');
            $fixtureInput = new ArrayInput(
                array(
                    'command' => 'doctrine:fixtures:load',
                    '--fixtures' => $fixturesPath,
                    '--append' => true
                )
            );
            $fixtureCommand->run($fixtureInput, $output);
        }

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
        $output->writeln('Done');
    }
}
