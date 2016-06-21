<?php

namespace Innova\AudioRecorderBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/30 11:50:15
 */
class Version20160330115013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_audio_recorder_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                max_recording_time INT DEFAULT 0 NOT NULL, 
                max_try INT DEFAULT 0 NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_audio_recorder_configuration
        ');
    }
}
