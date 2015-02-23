<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 10:37:35
 */
class Version20150223103734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD type VARCHAR(50) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD authors TEXT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD description TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD license TEXT NOT NULL
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_bundle.authors IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_bundle.license IS '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP type
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP authors
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP description
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP license
        ");
    }
}