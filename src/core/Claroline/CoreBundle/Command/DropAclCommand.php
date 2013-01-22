<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Development command removing the acl-related tables (no uninstallation
 * script is provided by the security bundle). Only called by the automatic
 * re-installation script (bin/factory_install_dev.php).
 */
class DropAclCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:security:drop_acl')
            ->setDescription('Drops ACL tables in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $connection = $container->get('security.acl.dbal.connection');
        $sm = $connection->getSchemaManager();
        $tableNames = $sm->listTableNames();
        $tables = array(
            'entry_table_name' => $container->getParameter('security.acl.dbal.entry_table_name'),
            'class_table_name' => $container->getParameter('security.acl.dbal.class_table_name'),
            'sid_table_name' => $container->getParameter('security.acl.dbal.sid_table_name'),
            'oid_ancestors_table_name' => $container->getParameter('security.acl.dbal.oid_ancestors_table_name'),
            'oid_table_name' => $container->getParameter('security.acl.dbal.oid_table_name'),
        );

        $absentTables = 0;

        foreach ($tables as $table) {
            if (in_array($table, $tableNames, true)) {
                $connection->exec("DROP TABLE {$table}");
            } else {
                ++$absentTables;
            }
        }

        if ($absentTables == 5) {
            $output->writeln('No ACL tables were found.');
        } else {
            $output->writeln('ACL tables have been successfully deleted.');
        }
    }
}
