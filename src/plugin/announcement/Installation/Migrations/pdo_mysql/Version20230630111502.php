<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/30 11:15:03
 */
final class Version20230630111502 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD email_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8C131A730F FOREIGN KEY (email_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_79BF2C8C131A730F ON claro_announcement_aggregate (email_template_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            DROP FOREIGN KEY FK_79BF2C8C131A730F
        ');
        $this->addSql('
            DROP INDEX IDX_79BF2C8C131A730F ON claro_announcement_aggregate
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            DROP email_template_id
        ');
    }
}
