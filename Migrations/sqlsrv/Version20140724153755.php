<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/24 03:37:59
 */
class Version20140724153755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_security_token (
                id INT IDENTITY NOT NULL, 
                token_name NVARCHAR(255) NOT NULL, 
                token NVARCHAR(255) NOT NULL, 
                ip_address NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B3A67A403C274B64 ON claro_security_token (token_name) 
            WHERE token_name IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_security_token
        ");
    }
}