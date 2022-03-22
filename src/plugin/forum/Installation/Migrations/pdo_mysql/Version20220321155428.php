<?php

namespace Claroline\ForumBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/21 03:54:29
 */
class Version20220321155428 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum_message 
            DROP FOREIGN KEY FK_6A49AC0EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum_message 
            DROP FOREIGN KEY FK_6A49AC0EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }
}
