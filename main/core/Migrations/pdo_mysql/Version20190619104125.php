<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/06/19 10:41:30
 */
class Version20190619104125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace  
            ADD access_code VARCHAR(255) DEFAULT NULL, 
            ADD allowed_ips LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json_array)", 
            CHANGE start_date accessible_from DATETIME DEFAULT NULL, 
            CHANGE end_date accessible_until DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace   
            CHANGE accessible_from start_date DATETIME DEFAULT NULL, 
            CHANGE accessible_until end_date DATETIME DEFAULT NULL, 
            DROP access_code, 
            DROP allowed_ips
        ');
    }
}
