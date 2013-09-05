<?php

namespace Claroline\ForumBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 09:17:59
 */
class Version20130809091758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum (
                id INT AUTO_INCREMENT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_F2869DFB87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INT AUTO_INCREMENT NOT NULL, 
                subject_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_6A49AC0E23EDC87 (subject_id), 
                INDEX IDX_6A49AC0EA76ED395 (user_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_options (
                id INT AUTO_INCREMENT NOT NULL, 
                subjects INT NOT NULL, 
                messages INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INT AUTO_INCREMENT NOT NULL, 
                forum_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_273AA20B29CCBAD0 (forum_id), 
                INDEX IDX_273AA20BA76ED395 (user_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum 
            ADD CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
            REFERENCES claro_forum_subject (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20B29CCBAD0
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP FOREIGN KEY FK_6A49AC0E23EDC87
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