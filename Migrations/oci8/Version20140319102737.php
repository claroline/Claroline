<?php

namespace Icap\DropzoneBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/19 10:27:41
 */
class Version20140319102737 extends AbstractMigration
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP (
                correctionDenied, correctionDeniedComment
            )
        ");
    }
}