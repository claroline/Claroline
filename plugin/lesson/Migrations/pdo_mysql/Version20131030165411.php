<?php

namespace Icap\LessonBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/10/30 04:54:12
 */
class Version20131030165411 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8CCDF80196
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8CCDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES icap__lesson (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP FOREIGN KEY FK_D9B3613079066886
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B3613079066886 FOREIGN KEY (root_id) 
            REFERENCES icap__lesson_chapter (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP FOREIGN KEY FK_D9B3613079066886
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B3613079066886 FOREIGN KEY (root_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8CCDF80196
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8CCDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES icap__lesson (id)
        ');
    }
}
