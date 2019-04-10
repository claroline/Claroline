<?php

namespace Claroline\DropZoneBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/04/09 12:03:29
 */
class Version20190409120322 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            ADD team_uuid VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            ADD corrector_displayed TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_correction 
            ADD team_uuid VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_correction 
            DROP team_uuid
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            DROP team_uuid
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            DROP corrector_displayed
        ');
    }
}
