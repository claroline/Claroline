<?php

namespace Claroline\ForumBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/18 02:34:44
 */
class Version20141118143441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_notification 
            ADD self_activation BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_notification 
            ADD CONSTRAINT DF_1330B0B6_8F0ED9D8 DEFAULT '1' FOR self_activation
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_notification 
            DROP COLUMN self_activation
        ");
    }
}