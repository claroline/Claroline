<?php

namespace Icap\DropzoneBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/03/26 08:35:57
 */
class Version20140326083554 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN correction_instruction CLOB(1M) DEFAULT NULL 
            ADD COLUMN success_message CLOB(1M) DEFAULT NULL 
            ADD COLUMN fail_message CLOB(1M) DEFAULT NULL 
            DROP COLUMN correctionInstruction 
            DROP COLUMN successMessage 
            DROP COLUMN failMessage
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN correctionInstruction CLOB(1M) DEFAULT NULL 
            ADD COLUMN successMessage CLOB(1M) DEFAULT NULL 
            ADD COLUMN failMessage CLOB(1M) DEFAULT NULL 
            DROP COLUMN correction_instruction 
            DROP COLUMN success_message 
            DROP COLUMN fail_message
        ');
    }
}
