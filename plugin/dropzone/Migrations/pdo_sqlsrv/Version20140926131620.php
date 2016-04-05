<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/26 01:16:25
 */
class Version20140926131620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD event_agenda_drop INT
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD event_agenda_correction INT
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23E6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC238D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone (event_agenda_drop) 
            WHERE event_agenda_drop IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC238D9E1321 ON icap__dropzonebundle_dropzone (event_agenda_correction) 
            WHERE event_agenda_correction IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN event_agenda_drop
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN event_agenda_correction
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT FK_6782FC23E6B974D2
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT FK_6782FC238D9E1321
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_6782FC23E6B974D2'
            ) 
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT UNIQ_6782FC23E6B974D2 ELSE 
            DROP INDEX UNIQ_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_6782FC238D9E1321'
            ) 
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT UNIQ_6782FC238D9E1321 ELSE 
            DROP INDEX UNIQ_6782FC238D9E1321 ON icap__dropzonebundle_dropzone
        ");
    }
}