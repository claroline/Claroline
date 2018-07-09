<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/03 11:38:32
 */
class Version20180703113830 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab
            ADD poster VARCHAR(255) DEFAULT NULL,
            ADD longTitle LONGTEXT DEFAULT NULL,
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_home_tab SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A9744CCED17F50A6 ON claro_home_tab (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_A9744CCED17F50A6 ON claro_home_tab
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab
            DROP poster,
            DROP longTitle,
            DROP uuid
        ');
    }
}
