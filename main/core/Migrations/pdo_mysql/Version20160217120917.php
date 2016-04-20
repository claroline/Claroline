<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/17 12:09:20
 */
class Version20160217120917 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE group_organization (
                group_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_2DA82945FE54D947 (group_id), 
                INDEX IDX_2DA8294532C8A3DE (organization_id), 
                PRIMARY KEY(group_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
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
            ALTER TABLE group_organization 
            ADD CONSTRAINT FK_2DA82945FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_organization 
            ADD CONSTRAINT FK_2DA8294532C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_group_organization 
            ADD CONSTRAINT FK_B912197E32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_group_organization 
            ADD CONSTRAINT FK_B912197EFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            ADD PRIMARY KEY (user_id, organization_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE group_organization
        ');
        $this->addSql('
            DROP TABLE claro_group_organization
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            ADD PRIMARY KEY (organization_id, user_id)
        ');
    }
}
