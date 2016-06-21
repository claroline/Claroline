<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/18 03:18:57
 */
class Version20140918151832 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD author VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD author VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_message 
            DROP author
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP author
        ');
    }
}
