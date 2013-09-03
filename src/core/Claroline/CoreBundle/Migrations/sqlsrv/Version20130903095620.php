<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                resource_type_id INT, 
                name NVARCHAR(255), 
                async BIT, 
                is_custom BIT NOT NULL, 
                is_form BIT NOT NULL, 
                permRequired NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F57E52B98EC6B7B ON claro_menu_action (resource_type_id)
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