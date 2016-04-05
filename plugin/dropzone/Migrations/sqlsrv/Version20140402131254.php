<?php

namespace Icap\DropzoneBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/02 01:12:57
 */
class Version20140402131254 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD auto_close_opened_drops_when_time_is_up BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD auto_close_state NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT DF_6782FC23_38E9F56B DEFAULT 'waiting' FOR auto_close_state
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN auto_close_opened_drops_when_time_is_up
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN auto_close_state
        ");
    }
}