<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RestoreTabsColorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:tabs:restore_color')
            ->setDescription('Restores color for tabs and containers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>  Restoring tabs color...</info>');
        $this->restoreTabsColor();
        $output->writeln('<info>  Tabs color restored.</info>');

        $output->writeln('<info>  Restoring containers color...</info>');
        $this->restoreContainersColor();
        $output->writeln('<info>  Containers color restored.</info>');
    }

    private function restoreTabsColor()
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        //configs are stored in a json array so we can't go full sql
        $sql = "SELECT * FROM `claro_home_tab_config_temp` WHERE `details` LIKE '%color%'";

        $configs = $connection->query($sql);

        while ($row = $configs->fetch()) {
            $details = json_decode($row['details'], true);

            if (isset($details['color'])) {
                $sql = "
                    UPDATE claro_home_tab_config SET color = '{$details['color']}' WHERE home_tab_id = {$row['id']}
                ";
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            }
        }
    }

    private function restoreContainersColor()
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        //configs are stored in a json array so we can't go full sql
        $sql = "SELECT * FROM `claro_widget_display_config_temp` WHERE `color` IS NOT NULL";

        $configs = $connection->query($sql);

	while ($row = $configs->fetch())  
	{
                $sql = "
                    UPDATE claro_widget_container_config SET borderColor = '{$row['color']}' WHERE widget_container_id = {$row['id']}
                ";
                $stmt = $connection->prepare($sql);
                $stmt->execute();
        }
    }
}
