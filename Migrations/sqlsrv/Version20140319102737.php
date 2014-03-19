<?php

namespace Icap\DropzoneBundle\Migrations\sqlsrv;

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
            ADD correctionDenied BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD correctionDeniedComment VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDenied
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDeniedComment
        ");
    }
}