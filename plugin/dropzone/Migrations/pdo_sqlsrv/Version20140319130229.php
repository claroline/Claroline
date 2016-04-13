<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/03/19 01:02:32
 */
class Version20140319130229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_correction 
            ADD correctionDenied BIT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_correction 
            ADD correctionDeniedComment VARCHAR(MAX)
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD diplay_corrections_to_learners BIT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD allow_correction_deny BIT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDenied
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDeniedComment
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN diplay_corrections_to_learners
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN allow_correction_deny
        ');
    }
}
