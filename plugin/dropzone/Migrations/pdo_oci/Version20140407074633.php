<?php

namespace Icap\DropzoneBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/07 07:46:37
 */
class Version20140407074633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD (
                auto_closed_drop NUMBER(1) DEFAULT '0' NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone MODIFY (
                auto_close_opened_drops_when_time_is_up NUMBER(1) DEFAULT '0'
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            DROP (auto_closed_drop)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone MODIFY (
                auto_close_opened_drops_when_time_is_up NUMBER(1) DEFAULT NULL
            )
        ");
    }
}