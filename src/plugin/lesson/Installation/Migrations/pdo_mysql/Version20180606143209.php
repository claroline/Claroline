<?php

namespace Icap\LessonBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:50:19
 */
class Version20180606143209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__lesson_chapter (
                id INT AUTO_INCREMENT NOT NULL, 
                lesson_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                text LONGTEXT DEFAULT NULL, 
                slug VARCHAR(128) NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3D7E3C8C989D9B62 (slug), 
                UNIQUE INDEX UNIQ_3D7E3C8CD17F50A6 (uuid), 
                INDEX IDX_3D7E3C8CCDF80196 (lesson_id), 
                INDEX IDX_3D7E3C8C727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__lesson (
                id INT AUTO_INCREMENT NOT NULL, 
                root_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D9B36130D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_D9B3613079066886 (root_id), 
                UNIQUE INDEX UNIQ_D9B36130B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8CCDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES icap__lesson (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B3613079066886 FOREIGN KEY (root_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B36130B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8C727ACA70
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP FOREIGN KEY FK_D9B3613079066886
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8CCDF80196
        ');
        $this->addSql('
            DROP TABLE icap__lesson_chapter
        ');
        $this->addSql('
            DROP TABLE icap__lesson
        ');
    }
}
