<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/14 16:00:00
 */
class Version20210914160000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_evaluation as e
            LEFT JOIN claro_resource_user_evaluation as ue ON (e.resource_user_evaluation = ue.id)
            LEFT JOIN claro_resource_node AS n ON (ue.resource_node = n.id)
            SET e.progression_max = 100
            WHERE e.progression_max IS NULL
              AND n.mime_type = "custom/ujm_exercise"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
