<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/10/09 04:06:42
 */
class Version20151009160642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_friend_request 
            ADD allow_authentication TINYINT(1) NOT NULL, 
            ADD create_user_if_missing TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_friend_request 
            DROP allow_authentication, 
            DROP create_user_if_missing
        ');
    }
}
