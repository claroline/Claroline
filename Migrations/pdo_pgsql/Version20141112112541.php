<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/12 11:25:43
 */
class Version20141112112541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_tool_rights (
                id SERIAL NOT NULL, 
                role_id INT NOT NULL, 
                ordered_tool_id INT NOT NULL, 
                mask INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EFEDEC7ED60322AC ON claro_tool_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EFEDEC7EBAC1B1D7 ON claro_tool_rights (ordered_tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_rights_unique_ordered_tool_role ON claro_tool_rights (ordered_tool_id, role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_tool_mask_decoder (
                id SERIAL NOT NULL, 
                tool_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                granted_icon_class VARCHAR(255) NOT NULL, 
                denied_icon_class VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_323623448F7B22CC ON claro_tool_mask_decoder (tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder (tool_id, name)
        ");
        $this->addSql("
            ALTER TABLE claro_tool_rights 
            ADD CONSTRAINT FK_EFEDEC7ED60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_tool_rights 
            ADD CONSTRAINT FK_EFEDEC7EBAC1B1D7 FOREIGN KEY (ordered_tool_id) 
            REFERENCES claro_ordered_tool (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_tool_mask_decoder 
            ADD CONSTRAINT FK_323623448F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_tool_rights
        ");
        $this->addSql("
            DROP TABLE claro_tool_mask_decoder
        ");
    }
}