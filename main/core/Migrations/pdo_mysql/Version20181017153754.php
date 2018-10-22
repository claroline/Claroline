<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/17 03:37:56
 */
class Version20181017153754 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD desktop_category VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            CREATE TABLE claro_tools_role (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                visible TINYINT(1) NOT NULL, 
                locked TINYINT(1) NOT NULL, 
                tool_order INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F97E6E3ED17F50A6 (uuid), 
                INDEX IDX_F97E6E3E8F7B22CC (tool_id), 
                INDEX IDX_F97E6E3ED60322AC (role_id), 
                UNIQUE INDEX tool_role_unique (tool_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_tools_role 
            ADD CONSTRAINT FK_F97E6E3E8F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tools_role 
            ADD CONSTRAINT FK_F97E6E3ED60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP desktop_category
        ');
        $this->addSql('
            DROP TABLE claro_tools_role
        ');
    }
}
