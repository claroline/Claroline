<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/25 05:29:29
 */
class Version20181025172928 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools_role 
            ADD display VARCHAR(255) DEFAULT NULL, 
            DROP visible, 
            DROP locked
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools_role 
            ADD visible TINYINT(1) NOT NULL, 
            ADD locked TINYINT(1) NOT NULL, 
            DROP display
        ');
    }
}
