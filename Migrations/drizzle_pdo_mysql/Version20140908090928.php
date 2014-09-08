<?php

namespace Icap\DropzoneBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/08 09:09:36
 */
class Version20140908090928 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC238D9E1321
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC23E6B974D2
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC238D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23E6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC23E6B974D2
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP FOREIGN KEY FK_6782FC238D9E1321
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23E6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC238D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id)
        ");
    }
}