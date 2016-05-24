<?php

namespace Claroline\VideoPlayerBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/24 12:13:50
 */
class Version20160524121348 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_video_track (
                id INT AUTO_INCREMENT NOT NULL, 
                video_id INT DEFAULT NULL, 
                lang VARCHAR(255) DEFAULT NULL, 
                `label` VARCHAR(255) DEFAULT NULL, 
                kind VARCHAR(255) NOT NULL, 
                is_default TINYINT(1) NOT NULL, 
                trackFile_id INT DEFAULT NULL, 
                INDEX IDX_D25DC06529C1004E (video_id), 
                INDEX IDX_D25DC065ED87669A (trackFile_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_video_track 
            ADD CONSTRAINT FK_D25DC06529C1004E FOREIGN KEY (video_id) 
            REFERENCES claro_file (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_video_track 
            ADD CONSTRAINT FK_D25DC065ED87669A FOREIGN KEY (trackFile_id) 
            REFERENCES claro_file (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_video_track
        ');
    }
}
