<?php

namespace Innova\PathBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/08 11:32:26
 */
class Version20140708113225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step_inherited_resources (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_C7E87ECC73B21E9C (step_id)
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step_inherited_resources
        ");
    }
}