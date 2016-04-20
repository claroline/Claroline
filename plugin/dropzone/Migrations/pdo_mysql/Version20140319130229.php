<?php

namespace Icap\DropzoneBundle\Migrations\pdo_mysql;

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
            ADD correctionDenied TINYINT(1) NOT NULL, 
            ADD correctionDeniedComment LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD diplay_corrections_to_learners TINYINT(1) NOT NULL, 
            ADD allow_correction_deny TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_correction 
            DROP correctionDenied, 
            DROP correctionDeniedComment
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP diplay_corrections_to_learners, 
            DROP allow_correction_deny
        ');
    }
}
