<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/29 09:07:56
 */
class Version20140729090754 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_security_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_name VARCHAR(255) NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                client_ip VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_B3A67A408FBFBD64 (client_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_security_token
        ");
    }
}