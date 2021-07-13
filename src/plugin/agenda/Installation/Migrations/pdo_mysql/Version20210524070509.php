<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/24 07:05:16
 */
class Version20210524070509 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD invitation_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5D2D03B8 FOREIGN KEY (invitation_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB5D2D03B8 ON claro_event (invitation_template_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB5D2D03B8
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB5D2D03B8 ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP invitation_template_id
        ');
    }
}
