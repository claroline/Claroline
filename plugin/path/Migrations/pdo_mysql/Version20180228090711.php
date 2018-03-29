<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/28 09:07:12
 */
class Version20180228090711 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            ADD source_uuid VARCHAR(36) DEFAULT NULL, 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE innova_step_inherited_resources SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C7E87ECCD17F50A6 ON innova_step_inherited_resources (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_C7E87ECCD17F50A6 ON innova_step_inherited_resources
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            DROP source_uuid, 
            DROP uuid
        ');
    }
}
