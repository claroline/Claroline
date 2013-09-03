<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:56:21
 */
class Version20130903095620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                is_custom BOOLEAN NOT NULL, 
                is_form BOOLEAN NOT NULL, 
                permRequired VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
    }
}