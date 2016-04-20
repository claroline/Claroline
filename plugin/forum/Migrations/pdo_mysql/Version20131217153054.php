<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/12/17 03:30:55
 */
class Version20131217153054 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_forum_category (
                id INT AUTO_INCREMENT NOT NULL, 
                forum_id INT DEFAULT NULL, 
                created DATETIME NOT NULL, 
                modificationDate DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_2192ACF729CCBAD0 (forum_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_forum_category 
            ADD CONSTRAINT FK_2192ACF729CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20B29CCBAD0
        ');
        $this->addSql('
            DROP INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD isSticked TINYINT(1) NOT NULL, 
            CHANGE forum_id category_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B12469DE2 FOREIGN KEY (category_id) 
            REFERENCES claro_forum_category (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_273AA20B12469DE2 ON claro_forum_subject (category_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20B12469DE2
        ');
        $this->addSql('
            DROP TABLE claro_forum_category
        ');
        $this->addSql('
            DROP INDEX IDX_273AA20B12469DE2 ON claro_forum_subject
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP isSticked, 
            CHANGE category_id forum_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ');
    }
}
