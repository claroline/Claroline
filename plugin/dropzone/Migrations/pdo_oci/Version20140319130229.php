<?php

namespace Icap\DropzoneBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/19 01:02:32
 */
class Version20140319130229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD (
                correctionDenied NUMBER(1) NOT NULL, 
                correctionDeniedComment CLOB DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD (
                diplay_corrections_to_learners NUMBER(1) NOT NULL, 
                allow_correction_deny NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP (
                correctionDenied, correctionDeniedComment
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP (
                diplay_corrections_to_learners, 
                allow_correction_deny
            )
        ");
    }
}