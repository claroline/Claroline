<?php

namespace Claroline\AgendaBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/31 10:52:00
 */
class Version20190831105131 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD uuid VARCHAR(36) NOT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_event SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B1ADDDB5D17F50A6 ON claro_event (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_B1ADDDB5D17F50A6 ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP uuid, 
            DROP thumbnail
        ');
    }
}
