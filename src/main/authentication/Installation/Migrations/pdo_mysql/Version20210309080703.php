<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/09 08:07:05
 */
class Version20210309080703 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_api_token 
            ADD is_locked TINYINT(1) DEFAULT "0" NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user CHANGE is_locked is_locked TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_api_token 
            DROP is_locked
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user CHANGE is_locked is_locked TINYINT(1) NOT NULL
        ');
    }
}
