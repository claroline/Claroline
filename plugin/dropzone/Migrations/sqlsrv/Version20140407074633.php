<?php

namespace Icap\DropzoneBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/04/07 07:46:38
 */
class Version20140407074633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            ADD auto_closed_drop BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT DF_3AD19BA6_D8F7A5C7 DEFAULT '0' FOR auto_closed_drop
        ");
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone ALTER COLUMN auto_close_opened_drops_when_time_is_up BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT DF_6782FC23_70DF9A93 DEFAULT '0' FOR auto_close_opened_drops_when_time_is_up
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            DROP COLUMN auto_closed_drop
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP CONSTRAINT DF_6782FC23_70DF9A93
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone ALTER COLUMN auto_close_opened_drops_when_time_is_up BIT NOT NULL
        ');
    }
}
