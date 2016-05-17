<?php

namespace Icap\LessonBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/01/13 12:20:56
 */
class Version20160113122054 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8C727ACA70
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE SET NULL
        ');
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
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8C727ACA70
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ');
    }
}
