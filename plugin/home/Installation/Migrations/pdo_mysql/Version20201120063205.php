<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/11/20 06:32:28
 */
class Version20201120063205 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD context VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_home_tab SET context = `type`
        ');

        $this->addSql('
            UPDATE claro_home_tab SET `type` = "widgets"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP context
        ');
    }
}
