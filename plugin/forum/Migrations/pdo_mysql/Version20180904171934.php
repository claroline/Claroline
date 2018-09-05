<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/09/04 05:19:36
 */
class Version20180904171934 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_subject
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id)
            REFERENCES claro_forum (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user
            DROP FOREIGN KEY FK_2CFBFDC429CCBAD0
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user
            ADD CONSTRAINT FK_2CFBFDC429CCBAD0 FOREIGN KEY (forum_id)
            REFERENCES claro_forum (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_subject
            DROP FOREIGN KEY FK_273AA20B29CCBAD0
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user
            DROP FOREIGN KEY FK_2CFBFDC429CCBAD0
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user
            ADD CONSTRAINT FK_2CFBFDC429CCBAD0 FOREIGN KEY (forum_id)
            REFERENCES claro_forum (id)
        ');
    }
}
