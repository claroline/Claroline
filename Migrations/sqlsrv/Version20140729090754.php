<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/29 09:07:57
 */
class Version20140729090754 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_security_token (
                id INT IDENTITY NOT NULL, 
                client_name NVARCHAR(255) NOT NULL, 
                token NVARCHAR(255) NOT NULL, 
                client_ip NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B3A67A408FBFBD64 ON claro_security_token (client_name) 
            WHERE client_name IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_security_token
        ");
    }
}