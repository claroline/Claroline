<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/23 01:58:29
 */
class Version20140923135827 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_model_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node_id INT NOT NULL, 
                model_id INT NOT NULL, 
                isCopy TINYINT(1) NOT NULL, 
                INDEX IDX_F5D706351BAD783F (resource_node_id), 
                INDEX IDX_F5D706357975B7E7 (model_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706351BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706357975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_message CHANGE receiver_string receiver_string VARCHAR(2047) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_model_resource
        ");
        $this->addSql("
            ALTER TABLE claro_message CHANGE receiver_string receiver_string VARCHAR(1023) NOT NULL
        ");
    }
}