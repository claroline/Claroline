<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/02 07:18:36
 */
class Version20220302071821 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            CHANGE is_visible hidden TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD accessible_from DATETIME DEFAULT NULL, 
            ADD accessible_until DATETIME DEFAULT NULL, 
            ADD access_code VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_home_tab SET hidden = !hidden
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            CHANGE hidden is_visible TINYINT(1) NOT NULL, 
            DROP accessible_from, 
            DROP accessible_until, 
            DROP access_code
        ');
    }
}
