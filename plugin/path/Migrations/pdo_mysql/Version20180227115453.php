<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/27 11:54:54
 */
class Version20180227115453 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_step_secondary_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT NOT NULL, 
                resource_id INT NOT NULL, 
                resource_order INT NOT NULL, 
                inheritance_enabled TINYINT(1) NOT NULL,
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_4E895FCBD17F50A6 (uuid), 
                INDEX IDX_4E895FCB73B21E9C (step_id), 
                INDEX IDX_4E895FCB89329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            ADD CONSTRAINT FK_4E895FCB73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            ADD CONSTRAINT FK_4E895FCB89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            ADD resource_order INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_step_secondary_resource
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            DROP resource_order
        ');
    }
}
