<?php

namespace Claroline\ScormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/12/13 09:28:18
 */
class Version20161213092816 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_2004_resource 
            ADD hide_top_bar TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD exit_mode INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_resource 
            ADD hide_top_bar TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD exit_mode INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scorm_12_resource 
            DROP hide_top_bar, 
            DROP exit_mode
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_resource 
            DROP hide_top_bar, 
            DROP exit_mode
        ');
    }
}
