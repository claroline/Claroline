<?php

namespace Claroline\ForumBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/18 03:18:58
 */
class Version20140918151832 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD author NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD author NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP COLUMN author
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP COLUMN author
        ");
    }
}