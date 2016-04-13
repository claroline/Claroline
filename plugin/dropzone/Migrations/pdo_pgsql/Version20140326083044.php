<?php

namespace Icap\DropzoneBundle\Migrations\pdo_pgsql;

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
            ADD correctionInstruction TEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD successMessage TEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD failMessage TEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP correctionInstruction
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP successMessage
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP failMessage
        ');
    }
}
