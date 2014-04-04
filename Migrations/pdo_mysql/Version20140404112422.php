<?php

namespace Icap\DropzoneBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/04 11:24:26
 */
class Version20140404112422 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop CHANGE auto_closed_drop auto_closed_drop TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD auto_close_opened_drops_when_time_is_up TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD auto_close_state VARCHAR(255) DEFAULT 'waiting' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop CHANGE auto_closed_drop auto_closed_drop TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP auto_close_opened_drops_when_time_is_up, 
            DROP auto_close_state
        ");
    }
}