<?php

namespace Claroline\ForumBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/04 05:02:30
 */
class Version20130904170227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message CHANGE id id INT NOT NULL, 
            CHANGE content content LONGTEXT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject CHANGE id id INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_options CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message CHANGE id id INT AUTO_INCREMENT NOT NULL, 
            CHANGE content content VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
    }
}