<?php

namespace Innova\CollecticielBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/26 08:30:48
 */
class Version20140326083044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD (
                correctionInstruction CLOB DEFAULT NULL, 
                successMessage CLOB DEFAULT NULL, 
                failMessage CLOB DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            DROP (
                correctionInstruction, successMessage, 
                failMessage
            )
        ");
    }
}