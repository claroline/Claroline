<?php

namespace Icap\DropzoneBundle\Migrations\drizzle_pdo_mysql;

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
            ADD correction_instruction TEXT DEFAULT NULL, 
            ADD success_message TEXT DEFAULT NULL, 
            ADD fail_message TEXT DEFAULT NULL, 
            DROP correctionInstruction, 
            DROP successMessage, 
            DROP failMessage
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD correctionInstruction TEXT DEFAULT NULL, 
            ADD successMessage TEXT DEFAULT NULL, 
            ADD failMessage TEXT DEFAULT NULL, 
            DROP correction_instruction, 
            DROP success_message, 
            DROP fail_message
        ");
    }
}