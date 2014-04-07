<?php

namespace Icap\DropzoneBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/07 07:46:38
 */
class Version20140407074633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD auto_closed_drop BOOLEAN DEFAULT 'false' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone CHANGE auto_close_opened_drops_when_time_is_up auto_close_opened_drops_when_time_is_up BOOLEAN DEFAULT 'false' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            DROP auto_closed_drop
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone CHANGE auto_close_opened_drops_when_time_is_up auto_close_opened_drops_when_time_is_up BOOLEAN NOT NULL
        ");
    }
}