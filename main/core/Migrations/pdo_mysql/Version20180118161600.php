<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/01/18 04:16:01
 */
class Version20180118161600 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_group_organization
        ');
        $this->addSql('
            DROP INDEX uniq_d90285452b6fcfb2 ON claro_workspace
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D9028545D17F50A6 ON claro_workspace (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD nb_attempts INT NOT NULL, 
            ADD nb_openings INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_group_organization (
                organization_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_B912197E32C8A3DE (organization_id), 
                INDEX IDX_B912197EFE54D947 (group_id), 
                PRIMARY KEY(organization_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_group_organization 
            ADD CONSTRAINT FK_B912197EFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_group_organization 
            ADD CONSTRAINT FK_B912197E32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP nb_attempts, 
            DROP nb_openings
        ');
        $this->addSql('
            DROP INDEX uniq_d9028545d17f50a6 ON claro_workspace
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (uuid)
        ');
    }
}
