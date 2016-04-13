<?php

namespace Icap\DropzoneBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/26 01:16:25
 */
class Version20140926131620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN event_agenda_drop INTEGER DEFAULT NULL 
            ADD COLUMN event_agenda_correction INTEGER DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23E6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC238D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone (event_agenda_drop)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6782FC238D9E1321 ON icap__dropzonebundle_dropzone (event_agenda_correction)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN event_agenda_drop 
            DROP COLUMN event_agenda_correction
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC23E6B974D2
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC238D9E1321
        ');
        $this->addSql('
            DROP INDEX UNIQ_6782FC23E6B974D2
        ');
        $this->addSql('
            DROP INDEX UNIQ_6782FC238D9E1321
        ');
    }
}
