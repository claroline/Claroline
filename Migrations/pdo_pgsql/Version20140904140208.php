<?php

namespace Icap\DropzoneBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/04 02:02:12
 */
class Version20140904140208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_6782FC23E6B974D2
        ");
        $this->addSql("
            DROP INDEX IDX_6782FC238D9E1321
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD force_comment_in_correction BOOLEAN NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone (event_agenda_drop)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC238D9E1321 ON icap__dropzonebundle_dropzone (event_agenda_correction)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_6782FC23E6B974D2
        ");
        $this->addSql("
            DROP INDEX UNIQ_6782FC238D9E1321
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP force_comment_in_correction
        ");
        $this->addSql("
            CREATE INDEX IDX_6782FC23E6B974D2 ON icap__dropzonebundle_dropzone (event_agenda_drop)
        ");
        $this->addSql("
            CREATE INDEX IDX_6782FC238D9E1321 ON icap__dropzonebundle_dropzone (event_agenda_correction)
        ");
    }
}