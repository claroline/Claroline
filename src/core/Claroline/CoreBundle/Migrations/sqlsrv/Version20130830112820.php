<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/30 11:28:21
 */
class Version20130830112820 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_mask_decoder (
                id INT IDENTITY NOT NULL, 
                resource_type_id INT NOT NULL, 
                value INT NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_39D93F4298EC6B7B ON claro_resource_mask_decoder (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_action (
                id INT IDENTITY NOT NULL, 
                resource_type_id INT, 
                name NVARCHAR(255), 
                async BIT, 
                permRequired NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7EE4A91298EC6B7B ON claro_resource_action (resource_type_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_mask_decoder 
            ADD CONSTRAINT FK_39D93F4298EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_action 
            ADD CONSTRAINT FK_7EE4A91298EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD mask INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN can_delete
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN can_open
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN can_edit
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN can_copy
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN can_export
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_resource_mask_decoder
        ");
        $this->addSql("
            DROP TABLE claro_resource_action
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_delete BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_open BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_edit BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_copy BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD can_export BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP COLUMN mask
        ");
    }
}