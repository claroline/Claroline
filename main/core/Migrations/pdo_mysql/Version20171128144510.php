<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/28 02:45:12
 */
class Version20171128144510 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_facet
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_facet SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DCBA6D3AD17F50A6 ON claro_facet (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_panel_facet_role SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A66BF654D17F50A6 ON claro_panel_facet_role (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_general_facet_preference SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_38AACF88D17F50A6 ON claro_general_facet_preference (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_field_facet_value SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_35307C0AD17F50A6 ON claro_field_facet_value (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_field_facet SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F6C21DB2D17F50A6 ON claro_field_facet (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_panel_facet SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DA3985FD17F50A6 ON claro_panel_facet (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_field_facet_choice SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E2001DD17F50A6 ON claro_field_facet_choice (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_DCBA6D3AD17F50A6 ON claro_facet
        ');
        $this->addSql('
            ALTER TABLE claro_facet
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_F6C21DB2D17F50A6 ON claro_field_facet
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_E2001DD17F50A6 ON claro_field_facet_choice
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_35307C0AD17F50A6 ON claro_field_facet_value
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_38AACF88D17F50A6 ON claro_general_facet_preference
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_DA3985FD17F50A6 ON claro_panel_facet
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_A66BF654D17F50A6 ON claro_panel_facet_role
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role
            DROP uuid
        ');
    }
}
