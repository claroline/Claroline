<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/25 05:42:14
 */
class Version20160225174213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro__location_organization (
                organization_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_C4EBDE032C8A3DE (organization_id), 
                INDEX IDX_C4EBDE064D218E (location_id), 
                PRIMARY KEY(organization_id, location_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD CONSTRAINT FK_C4EBDE032C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD CONSTRAINT FK_C4EBDE064D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP TABLE claro_user_organization
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

    public function down(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_user_organization (
                organization_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_9F29A0F732C8A3DE (organization_id), 
                INDEX IDX_9F29A0F7A76ED395 (user_id), 
                PRIMARY KEY(organization_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_user_organization 
            ADD CONSTRAINT FK_9F29A0F732C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_organization 
            ADD CONSTRAINT FK_9F29A0F7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP TABLE claro__location_organization
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
}
