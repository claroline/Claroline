<?php

namespace Icap\DropzoneBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/19 01:02:33
 */
class Version20140319130229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD correctionDenied BOOLEAN NOT NULL, 
            ADD correctionDeniedComment TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD diplay_corrections_to_learners BOOLEAN NOT NULL, 
            ADD allow_correction_deny BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP correctionDenied, 
            DROP correctionDeniedComment
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP diplay_corrections_to_learners, 
            DROP allow_correction_deny
        ");
    }
}