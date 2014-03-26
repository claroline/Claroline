<?php

namespace Icap\DropzoneBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/26 08:35:57
 */
class Version20140326083554 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD correction_instruction TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD success_message TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD fail_message TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP correctionInstruction
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP successMessage
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP failMessage
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD correctionInstruction TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD successMessage TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD failMessage TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP correction_instruction
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP success_message
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP fail_message
        ");
    }
}