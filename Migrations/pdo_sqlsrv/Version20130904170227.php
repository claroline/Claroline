<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE claro_forum ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER COLUMN content VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER COLUMN id INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_options ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER COLUMN content NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum ALTER COLUMN id INT IDENTITY NOT NULL
        ");
    }
}