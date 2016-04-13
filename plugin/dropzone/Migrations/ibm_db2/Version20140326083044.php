<?php

namespace Icap\DropzoneBundle\Migrations\ibm_db2;

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
            ADD COLUMN correctionInstruction CLOB(1M) DEFAULT NULL 
            ADD COLUMN successMessage CLOB(1M) DEFAULT NULL 
            ADD COLUMN failMessage CLOB(1M) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN correctionInstruction 
            DROP COLUMN successMessage 
            DROP COLUMN failMessage
        ');
    }
}
