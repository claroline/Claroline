<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/04/12 01:49:01
 */
class Version20180412134900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_claco_form SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_ACB82378D17F50A6 ON claro_clacoformbundle_claco_form (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            ADD help VARCHAR(255) DEFAULT NULL, 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_field SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F84976F7D17F50A6 ON claro_clacoformbundle_field (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_category 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_category SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E2D499A8D17F50A6 ON claro_clacoformbundle_category (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_keyword 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_keyword SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CCDC13B7D17F50A6 ON claro_clacoformbundle_keyword (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_field_choice_category SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1F7C5EF7D17F50A6 ON claro_clacoformbundle_field_choice_category (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_comment 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_comment SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_23B30E0D17F50A6 ON claro_clacoformbundle_comment (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_entry SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_889DAEDFD17F50A6 ON claro_clacoformbundle_entry (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry_user 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_entry_user SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_7036190CD17F50A6 ON claro_clacoformbundle_entry_user (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field_value 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_clacoformbundle_field_value SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B481BDB9D17F50A6 ON claro_clacoformbundle_field_value (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_E2D499A8D17F50A6 ON claro_clacoformbundle_category
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_category 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_ACB82378D17F50A6 ON claro_clacoformbundle_claco_form
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_23B30E0D17F50A6 ON claro_clacoformbundle_comment
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_comment 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_889DAEDFD17F50A6 ON claro_clacoformbundle_entry
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_7036190CD17F50A6 ON claro_clacoformbundle_entry_user
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry_user 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_F84976F7D17F50A6 ON claro_clacoformbundle_field
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            DROP help, 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_1F7C5EF7D17F50A6 ON claro_clacoformbundle_field_choice_category
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_B481BDB9D17F50A6 ON claro_clacoformbundle_field_value
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field_value 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_CCDC13B7D17F50A6 ON claro_clacoformbundle_keyword
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_keyword 
            DROP uuid
        ');
    }
}
