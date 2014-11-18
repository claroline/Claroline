<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/18 02:34:43
 */
class Version20141118143441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_notification 
            ADD COLUMN self_activation BOOLEAN DEFAULT '1' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1330B0B629CCBAD0
        ");
        $this->addSql("
            DROP INDEX IDX_1330B0B6A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_notification AS 
            SELECT id, 
            forum_id, 
            user_id 
            FROM claro_forum_notification
        ");
        $this->addSql("
            DROP TABLE claro_forum_notification
        ");
        $this->addSql("
            CREATE TABLE claro_forum_notification (
                id INTEGER NOT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1330B0B629CCBAD0 FOREIGN KEY (forum_id) 
                REFERENCES claro_forum (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1330B0B6A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_notification (id, forum_id, user_id) 
            SELECT id, 
            forum_id, 
            user_id 
            FROM __temp__claro_forum_notification
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_notification
        ");
        $this->addSql("
            CREATE INDEX IDX_1330B0B629CCBAD0 ON claro_forum_notification (forum_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1330B0B6A76ED395 ON claro_forum_notification (user_id)
        ");
    }
}