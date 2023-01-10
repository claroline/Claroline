<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/13 12:56:58
 */
class Version20230113125657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD end_back_type LONGTEXT DEFAULT NULL, 
            ADD end_back_label LONGTEXT DEFAULT NULL, 
            ADD end_back_target LONGTEXT DEFAULT NULL,
            ADD show_workspace_certificates TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_exercise SET end_back_type = "workspace" WHERE end_navigation = 1
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP end_back_type, 
            DROP end_back_label, 
            DROP end_back_target,
            DROP show_workspace_certificates
        ');
    }
}
