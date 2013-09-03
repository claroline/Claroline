<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 05:00:43
 */
class Version20130903170043 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_39D93F4298EC6B7B (resource_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async TINYINT(1) DEFAULT NULL, 
                is_custom TINYINT(1) NOT NULL, 
                is_form TINYINT(1) NOT NULL, 
                value VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_resource_mask_decoder 
            ADD CONSTRAINT FK_39D93F4298EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD mask INT NOT NULL, 
            DROP can_delete, 
            DROP can_open, 
            DROP can_edit, 
            DROP can_copy, 
            DROP can_export
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_resource_mask_decoder
        ");
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_delete TINYINT(1) NOT NULL, 
            ADD can_open TINYINT(1) NOT NULL, 
            ADD can_edit TINYINT(1) NOT NULL, 
            ADD can_copy TINYINT(1) NOT NULL, 
            ADD can_export TINYINT(1) NOT NULL, 
            DROP mask
        ");
    }
}