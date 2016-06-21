<?php

namespace Icap\DropzoneBundle\Migrations\pdo_mysql;

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
            ADD correction_instruction LONGTEXT DEFAULT NULL, 
            ADD success_message LONGTEXT DEFAULT NULL, 
            ADD fail_message LONGTEXT DEFAULT NULL, 
            DROP correctionInstruction, 
            DROP successMessage, 
            DROP failMessage
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD correctionInstruction LONGTEXT DEFAULT NULL, 
            ADD successMessage LONGTEXT DEFAULT NULL, 
            ADD failMessage LONGTEXT DEFAULT NULL, 
            DROP correction_instruction, 
            DROP success_message, 
            DROP fail_message
        ');
    }
}
