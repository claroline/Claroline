<?php

namespace Claroline\BigBlueButtonBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/12/01 10:00:54
 */
class Version20201201100053 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_bigbluebuttonbundle_recording (
                id INT AUTO_INCREMENT NOT NULL, 
                recordId VARCHAR(255) NOT NULL,
                meeting_id INT DEFAULT NULL, 
                startTime VARCHAR(255) NOT NULL, 
                endTime VARCHAR(255) NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                participants INT NOT NULL, 
                medias LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_53FEEA5FD17F50A6 (uuid), 
                INDEX IDX_53FEEA5F67433D9C (meeting_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_recording 
            ADD CONSTRAINT FK_53FEEA5F67433D9C FOREIGN KEY (meeting_id) 
            REFERENCES claro_bigbluebuttonbundle_bbb (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_bigbluebuttonbundle_recording
        ');
    }
}
