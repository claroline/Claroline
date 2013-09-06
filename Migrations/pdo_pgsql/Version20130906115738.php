<?php

namespace Claroline\ForumBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/06 11:57:39
 */
class Version20130906115738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum (
                id SERIAL NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id SERIAL NOT NULL, 
                subject_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content TEXT NOT NULL, 
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E23EDC87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0EA76ED395 ON claro_forum_message (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_forum_options (
                id SERIAL NOT NULL, 
                subjects INT NOT NULL, 
                messages INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id SERIAL NOT NULL, 
                forum_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20BA76ED395 ON claro_forum_subject (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum 
            ADD CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
            REFERENCES claro_forum_subject (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B29CCBAD0
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP CONSTRAINT FK_6A49AC0E23EDC87
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum_message
        ");
        $this->addSql("
            DROP TABLE claro_forum_options
        ");
        $this->addSql("
            DROP TABLE claro_forum_subject
        ");
    }
}