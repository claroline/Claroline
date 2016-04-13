<?php

namespace Icap\DropzoneBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/04/02 01:12:57
 */
class Version20140402131254 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD (
                auto_close_opened_drops_when_time_is_up NUMBER(1) NOT NULL, 
                auto_close_state VARCHAR2(255) DEFAULT 'waiting' NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP (
                auto_close_opened_drops_when_time_is_up, 
                auto_close_state
            )
        ');
    }
}
