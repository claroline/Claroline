<?php

namespace Icap\DropzoneBundle\Migrations\ibm_db2;

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
            ADD COLUMN correctionDenied SMALLINT NOT NULL 
            ADD COLUMN correctionDeniedComment CLOB(1M) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN diplay_corrections_to_learners SMALLINT NOT NULL 
            ADD COLUMN allow_correction_deny SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP COLUMN correctionDenied 
            DROP COLUMN correctionDeniedComment
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN diplay_corrections_to_learners 
            DROP COLUMN allow_correction_deny
        ");
    }
}