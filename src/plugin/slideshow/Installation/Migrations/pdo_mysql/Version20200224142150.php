<?php

namespace Claroline\SlideshowBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:08:20
 */
class Version20200224142150 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_slide (
                id INT AUTO_INCREMENT NOT NULL, 
                slideshow_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                slide_order INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_F8EC3970D17F50A6 (uuid), 
                INDEX IDX_F8EC39708B14E343 (slideshow_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_slideshow (
                id INT AUTO_INCREMENT NOT NULL, 
                auto_play TINYINT(1) DEFAULT '0' NOT NULL, 
                slide_interval INT NOT NULL, 
                show_overview TINYINT(1) DEFAULT '0' NOT NULL, 
                show_controls TINYINT(1) DEFAULT '0' NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_3326614CD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_3326614CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_slide 
            ADD CONSTRAINT FK_F8EC39708B14E343 FOREIGN KEY (slideshow_id) 
            REFERENCES claro_slideshow (id)
        ');
        $this->addSql('
            ALTER TABLE claro_slideshow 
            ADD CONSTRAINT FK_3326614CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_slide 
            DROP FOREIGN KEY FK_F8EC39708B14E343
        ');
        $this->addSql('
            DROP TABLE claro_slide
        ');
        $this->addSql('
            DROP TABLE claro_slideshow
        ');
    }
}
