<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/26 03:32:24
 */
class Version20180826153207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD columnsFilterable TINYINT(1) NOT NULL,
            ADD count TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD columnsFilterable TINYINT(1) NOT NULL,
            ADD maxResults INT DEFAULT NULL,
            ADD count TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP columnsFilterable,
            DROP count
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP columnsFilterable,
            DROP maxResults,
            DROP count
        ');
    }
}
