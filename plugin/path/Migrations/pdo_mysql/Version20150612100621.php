<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/06/12 10:06:23
 */
class Version20150612100621 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856788BD9C1F
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources CHANGE resource_id resource_id INT NOT NULL, 
            CHANGE step_id step_id INT NOT NULL
        ');
        $this->addSql('SET foreign_key_checks = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856788BD9C1F
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources CHANGE step_id step_id INT DEFAULT NULL, 
            CHANGE resource_id resource_id INT DEFAULT NULL
        ');
    }
}
