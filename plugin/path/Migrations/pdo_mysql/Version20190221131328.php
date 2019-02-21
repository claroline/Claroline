<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/02/21 01:13:34
 */
class Version20190221131328 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP lvl
        ');
        $this->addSql('
            DROP INDEX UNIQ_4E895FCBD17F50A6 ON innova_step_secondary_resource
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            DROP inheritance_enabled,
            DROP uuid
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step 
            ADD lvl INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            ADD inheritance_enabled TINYINT(1) NOT NULL,
            ADD uuid VARCHAR(36) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_4E895FCBD17F50A6 ON innova_step_secondary_resource (uuid)
        ');
    }
}
