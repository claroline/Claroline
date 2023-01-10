<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/13 12:41:35
 */
class Version20230113124133 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD end_navigation TINYINT(1) NOT NULL, 
            ADD end_back_type LONGTEXT DEFAULT NULL, 
            ADD end_back_label LONGTEXT DEFAULT NULL, 
            ADD end_back_target LONGTEXT DEFAULT NULL, 
            ADD show_workspace_certificates TINYINT(1) NOT NULL,
            CHANGE show_overview show_overview TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE innova_path SET end_navigation = true, end_back_type = "workspace"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP end_navigation, 
            DROP end_back_type, 
            DROP end_back_label, 
            DROP end_back_target, 
            DROP show_workspace_certificates,
            CHANGE show_overview show_overview TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }
}
