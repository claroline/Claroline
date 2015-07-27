<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/27 02:11:51
 */
class Version20150727141151 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_api_claroline_access (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                access_token VARCHAR(255) NOT NULL, 
                INDEX IDX_2B10E8B119EB6921 (client_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_api_claroline_access 
            ADD CONSTRAINT FK_2B10E8B119EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_api_claroline_access
        ");
    }
}