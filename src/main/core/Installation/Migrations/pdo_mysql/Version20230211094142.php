<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/11 09:42:00
 */
class Version20230211094142 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // resource attempts
        $this->addSql('
            UPDATE claro_resource_evaluation SET progression_max = 100 WHERE progression_max IS NULL
        ');
        $this->addSql('
            UPDATE claro_resource_evaluation SET progression = ((progression / progression_max) * 100) WHERE progression_max != 0
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP progression_max
        ');

        // resource evaluations
        $this->addSql('
            UPDATE claro_resource_user_evaluation SET progression_max = 100 WHERE progression_max IS NULL
        ');
        $this->addSql('
            UPDATE claro_resource_user_evaluation SET progression = ((progression / progression_max) * 100) WHERE progression_max != 0
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP progression_max
        ');

        // workspace evaluations
        $this->addSql('
            UPDATE claro_workspace_evaluation SET progression_max = 100 WHERE progression_max IS NULL
        ');
        $this->addSql('
            UPDATE claro_workspace_evaluation SET progression = ((progression / progression_max) * 100) WHERE progression_max != 0
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP progression_max
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD progression_max INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD progression_max INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD progression_max INT DEFAULT NULL
        ');
    }
}
