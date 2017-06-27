<?php

namespace Icap\BadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/20 04:16:02
 */
class Version20170620161602 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_badge
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_74F39F0FD17F50A6 ON claro_badge (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_74F39F0FD17F50A6 ON claro_badge
        ');
        $this->addSql('
            ALTER TABLE claro_badge
            DROP uuid
        ');
    }
}
