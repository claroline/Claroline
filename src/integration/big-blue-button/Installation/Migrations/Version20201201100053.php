<?php

namespace Claroline\BigBlueButtonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 03:08:44
 */
final class Version20201201100053 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_bigbluebuttonbundle_bbb (
                id INT AUTO_INCREMENT NOT NULL, 
                welcome_message LONGTEXT DEFAULT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                new_tab TINYINT(1) NOT NULL, 
                moderator_required TINYINT(1) NOT NULL, 
                record TINYINT(1) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                activated TINYINT(1) NOT NULL, 
                server VARCHAR(255) DEFAULT NULL, 
                runningOn VARCHAR(255) DEFAULT NULL, 
                customUsernames TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CCC5E62ED17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_CCC5E62EB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_bigbluebuttonbundle_recording (
                id INT AUTO_INCREMENT NOT NULL, 
                meeting_id INT DEFAULT NULL, 
                recordId VARCHAR(255) NOT NULL, 
                startTime VARCHAR(255) NOT NULL, 
                endTime VARCHAR(255) NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                participants INT NOT NULL, 
                medias LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_53FEEA5FD17F50A6 (uuid), 
                INDEX IDX_53FEEA5F67433D9C (meeting_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_bbb 
            ADD CONSTRAINT FK_CCC5E62EB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_recording 
            ADD CONSTRAINT FK_53FEEA5F67433D9C FOREIGN KEY (meeting_id) 
            REFERENCES claro_bigbluebuttonbundle_bbb (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_bbb 
            DROP FOREIGN KEY FK_CCC5E62EB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_recording 
            DROP FOREIGN KEY FK_53FEEA5F67433D9C
        ');
        $this->addSql('
            DROP TABLE claro_bigbluebuttonbundle_bbb
        ');
        $this->addSql('
            DROP TABLE claro_bigbluebuttonbundle_recording
        ');
    }
}
