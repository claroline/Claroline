<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/14 03:07:34
 */
class Version20160614150733 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD isEditable TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD isRequired TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP isRequired
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            DROP isEditable
        ');
    }
}
