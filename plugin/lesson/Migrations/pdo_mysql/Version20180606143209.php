<?php

namespace Icap\LessonBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/06 02:32:11
 */
class Version20180606143209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__lesson_chapter
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3D7E3C8CD17F50A6 ON icap__lesson_chapter (uuid)
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__lesson
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D9B36130D17F50A6 ON icap__lesson (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D9B36130D17F50A6 ON icap__lesson
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_3D7E3C8CD17F50A6 ON icap__lesson_chapter
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP uuid
        ');
    }
}
