<?php

namespace Claroline\ScormBundle\Command;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exports a Resource in Scorm format.
 */
class ScormExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:scorm:export')
            ->setDescription('Exports a Resource in Scorm format.')
            ->addArgument('resource', InputArgument::REQUIRED, 'ID of the ResourceNode to export.')
            ->addOption('scorm-version', 'sv', InputOption::VALUE_OPTIONAL, 'SCORM version.', '2004')
            ->addOption('locale', 'l', InputOption::VALUE_OPTIONAL, 'Locale to use for the export', 'en')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('doctrine.orm.entity_manager');

        $resourceId = $input->getArgument('resource');

        /** @var ResourceNode $resourceNode */
        $resourceNode = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneBy([
            'id' => $resourceId,
        ]);

        if (!$resourceNode) {
            $output->writeln('<error>Unable to find the ResourceNode referenced by ID : '.$resourceId.'</error>');

            return false;
        }

        /* @var \Claroline\ScormBundle\Manager\ExportManager */
        $exporter = $this->getContainer()->get('claroline.scorm.export_manager');

        $version = $input->getOption('scorm-version');
        $output->writeln('Start creating SCORM ('.$version.') archive for ResourceNode : '.$resourceNode->getName().'...');

        return $exporter->export($resourceNode, $input->getOption('locale'), $version);
    }
}
