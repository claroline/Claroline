<?php

namespace Claroline\SamlBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/20 04:21:55
 */
class Version20200220162114 extends AbstractMigration
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
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_saml_id_entry
        ');
    }
}
