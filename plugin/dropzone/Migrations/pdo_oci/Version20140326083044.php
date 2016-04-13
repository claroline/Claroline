<?php

namespace Icap\DropzoneBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/03/26 08:30:48
 */
class Version20140326083044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD (
                correctionInstruction CLOB DEFAULT NULL, 
                successMessage CLOB DEFAULT NULL, 
                failMessage CLOB DEFAULT NULL
            )
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP (
                correctionInstruction, successMessage, 
                failMessage
            )
        ');
    }
}
