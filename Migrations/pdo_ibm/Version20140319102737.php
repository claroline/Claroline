<?php

namespace Icap\DropzoneBundle\Migrations\pdo_ibm;

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
            ADD COLUMN correctionDenied SMALLINT NOT NULL 
            ADD COLUMN correctionDeniedComment CLOB(1M) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDenied 
            DROP COLUMN correctionDeniedComment
        ");
    }
}