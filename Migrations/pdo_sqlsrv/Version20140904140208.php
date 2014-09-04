<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/04 02:02:13
 */
class Version20140904140208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD force_comment_in_correction BIT NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_6782FC23E6B974D2'
            ) 
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT IDX_6782FC23E6B974D2 ELSE 
            DROP INDEX IDX_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_6782FC238D9E1321'
            ) 
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT IDX_6782FC238D9E1321 ELSE 
            DROP INDEX IDX_6782FC238D9E1321 ON icap__dropzonebundle_dropzone
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
            DROP COLUMN force_comment_in_correction
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
        $this->addSql("
            CREATE INDEX IDX_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone (event_agenda_drop)
        ");
        $this->addSql("
            CREATE INDEX IDX_6782FC238D9E1321 ON icap__dropzonebundle_dropzone (event_agenda_correction)
        ");
    }
}