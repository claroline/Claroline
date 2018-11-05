<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/11/05 10:44:44
 */
class Version20181105104402 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD searchMode VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE claro_directory SET searchMode = "unified"
        ');

        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD searchMode VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE claro_widget_list SET searchMode = "unified"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP searchMode
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP searchMode
        ');
    }
}
