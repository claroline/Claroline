<?php

namespace Claroline\SamlBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:06:39
 */
class Version20200325090126 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_saml_id_entry (
                id VARCHAR(255) NOT NULL, 
                entityId VARCHAR(255) NOT NULL, 
                expiryTimestamp INT NOT NULL, 
                PRIMARY KEY(entityId, id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_saml_request_entry (
                id VARCHAR(255) NOT NULL, 
                parameters LONGTEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_saml_id_entry
        ');
        $this->addSql('
            DROP TABLE claro_saml_request_entry
        ');
    }
}
