<?php

namespace Icap\DropzoneBundle\Migrations\oci8;

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
            ADD (
                correction_instruction CLOB DEFAULT NULL, 
                success_message CLOB DEFAULT NULL, 
                fail_message CLOB DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP (
                correctionInstruction, successMessage, 
                failMessage
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD (
                correctionInstruction CLOB DEFAULT NULL, 
                successMessage CLOB DEFAULT NULL, 
                failMessage CLOB DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP (
                correction_instruction, success_message, 
                fail_message
            )
        ");
    }
}