<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/07 01:34:49
 */
final class Version20230707133448 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD pdf_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CCA5AA7D3 FOREIGN KEY (pdf_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_79BF2C8CCA5AA7D3 ON claro_announcement_aggregate (pdf_template_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            DROP FOREIGN KEY FK_79BF2C8CCA5AA7D3
        ');
        $this->addSql('
            DROP INDEX IDX_79BF2C8CCA5AA7D3 ON claro_announcement_aggregate
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            DROP pdf_template_id
        ');
    }
}
