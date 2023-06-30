<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/30 09:38:14
 */
final class Version20230630093814 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD announcement_template_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8C7B598FA6 FOREIGN KEY (announcement_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_79BF2C8C7B598FA6 ON claro_announcement_aggregate (announcement_template_id)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP FOREIGN KEY FK_79BF2C8C7B598FA6
        ");
        $this->addSql("
            DROP INDEX IDX_79BF2C8C7B598FA6 ON claro_announcement_aggregate
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP announcement_template_id
        ");
    }
}
