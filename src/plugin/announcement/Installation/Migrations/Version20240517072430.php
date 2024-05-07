<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/05/17 07:25:29
 */
final class Version20240517072430 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            DROP FOREIGN KEY FK_7C739377913AEA17
        ');
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            ADD CONSTRAINT FK_7C739377913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            DROP FOREIGN KEY FK_7C739377913AEA17
        ');
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            ADD CONSTRAINT FK_7C739377913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        ');
    }
}
