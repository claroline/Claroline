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
            ADD end_back_title LONGTEXT DEFAULT NULL, 
            ADD end_back_target LONGTEXT DEFAULT NULL, 
            CHANGE show_overview show_overview TINYINT(1) NOT NULL
        ');

        $this->addSql('UPDATE innova_path SET end_navigation = true');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP end_navigation, 
            DROP end_back_type, 
            DROP end_back_title, 
            DROP end_back_target, 
            CHANGE show_overview show_overview TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }
}
