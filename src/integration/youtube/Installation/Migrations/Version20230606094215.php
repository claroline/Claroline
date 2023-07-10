<?php

namespace Claroline\YouTubeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 03:09:12
 */
final class Version20230606094215 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_youtube_video (
                id INT AUTO_INCREMENT NOT NULL, 
                video_id VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_83751CC9D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_83751CC9B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_youtube_video 
            ADD CONSTRAINT FK_83751CC9B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_youtube_video 
            DROP FOREIGN KEY FK_83751CC9B87FAB32
        ');
        $this->addSql('
            DROP TABLE claro_youtube_video
        ');
    }
}
