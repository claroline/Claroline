<?php

namespace Claroline\DashboardBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/16 04:00:16
 */
class Version20160916160012 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_dashboard (
                id INT AUTO_INCREMENT NOT NULL,
                creator_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                name VARCHAR(50) NOT NULL,
                creation_date DATETIME NOT NULL,
                modification_date DATETIME NOT NULL,
                INDEX IDX_8027AA461220EA6 (creator_id),
                INDEX IDX_8027AA482D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_dashboard
            ADD CONSTRAINT FK_8027AA461220EA6 FOREIGN KEY (creator_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_dashboard
            ADD CONSTRAINT FK_8027AA482D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_dashboard
        ');
    }
}
