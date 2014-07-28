<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                token_name VARCHAR(255) NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                ip_address VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_B3A67A403C274B64 (token_name)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_security_token
        ");
    }
}