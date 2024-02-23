<?php

namespace Claroline\PeerTubeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/19 01:45:17
 */
final class Version20240219134516 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_peertube_video 
            ADD timecode_start INT DEFAULT NULL, 
            ADD timecode_end INT DEFAULT NULL, 
            ADD autoplay TINYINT(1) NOT NULL, 
            ADD looping TINYINT(1) NOT NULL, 
            ADD controls TINYINT(1) NOT NULL, 
            ADD peertubeLink TINYINT(1) NOT NULL,
            ADD resume TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_peertube_video 
            DROP timecode_start, 
            DROP timecode_end, 
            DROP autoplay, 
            DROP looping, 
            DROP controls, 
            DROP peertubeLink,
            DROP resume
        ');
    }
}
