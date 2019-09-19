<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceSlugBuilderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:resource-slug:builder')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'clear temporary')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'no dry run')
            ->setDescription('rebuild the resource slugs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $this->setLogger($consoleLogger);

        $conn = $this->getContainer()->get('doctrine.dbal.default_connection');

        $query = '
            CREATE TABLE claro_resource_node_temp_'.uniqid()."
            AS (SELECT * FROM claro_resource_node WHERE slug LIKE '%?%')
        ";
        try {
            $conn->query($query);
        } catch (\Exception $e) {
            $this->log('No need backup');
        }

        $this->log('Generating slugs for resources with bad slugs...');
        $sql = "
            UPDATE claro_resource_node node SET slug = REGEXP_REPLACE(SUBSTR(CONCAT(node.name, '-', node.id),1,100), '[^A-Za-z0-9]+', '-') WHERE slug LIKE '%?%'
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    private function setLogger($logger)
    {
        $this->consoleLogger = $logger;
    }

    private function log($log)
    {
        $this->consoleLogger->info($log);
    }
}
