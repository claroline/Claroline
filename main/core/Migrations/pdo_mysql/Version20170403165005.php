<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/04/03 04:50:07
 */
class Version20170403165005 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE workspace_organization (
                workspace_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_D212AD8082D40A1F (workspace_id), 
                INDEX IDX_D212AD8032C8A3DE (organization_id), 
                PRIMARY KEY(workspace_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            ADD CONSTRAINT FK_D212AD8082D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            ADD CONSTRAINT FK_D212AD8032C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE workspace_organization
        ');
    }
}
