<?php

namespace UJM\ExoBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $this->migrateDateData();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $this->dropExpertiseTables();
        }
    }

    private function migrateDateData()
    {
        $conn = $this->container->get('doctrine.dbal.default_connection');

        if (!$conn->getSchemaManager()->listTableDetails('ujm_exercise')->hasColumn('start_date')) {
            return; // migration has already been executed
        }

        $this->log('Moving date data from ujm_exercise to claro_resource_node...');

        $startQuery = '
            UPDATE claro_resource_node AS node
            JOIN ujm_exercise AS exo
            ON node.id = exo.resourceNode_id
            SET node.accessible_from = exo.start_date
            WHERE node.accessible_from IS NULL
            AND exo.start_date IS NOT NULL
        ';
        $endQuery = '
            UPDATE claro_resource_node AS node
            JOIN ujm_exercise AS exo
            ON node.id = exo.resourceNode_id
            SET node.accessible_until = exo.end_date
            WHERE node.accessible_until IS NULL
            AND exo.start_date IS NOT NULL
            AND exo.use_date_end = 1
        ';

        $conn->exec($startQuery);
        $conn->exec($endQuery);
    }

    private function dropExpertiseTables()
    {
        $schema = $this->container->get('doctrine.dbal.default_connection')
            ->getSchemaManager();
        $tableNames = $schema->listTableNames();
        $tablesToDrop = [
            'ujm_expertise_user',
            'ujm_expertise',
            'ujm_exercise_group',
            'ujm_planning',
            'ujm_group'
        ];

        foreach ($tablesToDrop as $tableName) {
            if (in_array($tableName, $tableNames)) {
                $this->log("Dropping {$tableName} table...");
                $schema->dropTable($tableName);
            }
        }
    }
}
