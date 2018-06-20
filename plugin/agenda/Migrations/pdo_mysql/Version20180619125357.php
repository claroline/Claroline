<?php

namespace Claroline\AgendaBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/19 12:53:59
 */
class Version20180619125357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event CHANGE start_date start_date DATETIME DEFAULT NULL,
            CHANGE end_date end_date DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event CHANGE start_date start_date INT DEFAULT NULL,
            CHANGE end_date end_date INT DEFAULT NULL
        ');
    }
}
