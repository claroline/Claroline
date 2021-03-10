<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/11 08:55:30
 */
class Version20210311085511 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD location_id INT DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB564D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB564D218E ON claro_event (location_id)
        ');
        $this->addSql('
            ALTER TABLE claro_task CHANGE done is_task_done TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB564D218E
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB564D218E ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP location_id, 
            DROP poster
        ');
        $this->addSql('
            ALTER TABLE claro_task CHANGE is_task_done done TINYINT(1) NOT NULL
        ');
    }
}
