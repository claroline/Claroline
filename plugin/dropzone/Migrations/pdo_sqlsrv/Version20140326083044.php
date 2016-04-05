<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/26 08:30:49
 */
class Version20140326083044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD correctionInstruction VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD successMessage VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD failMessage VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN correctionInstruction
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN successMessage
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN failMessage
        ");
    }
}