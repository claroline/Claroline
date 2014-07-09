<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/08 12:12:14
 */
class Version20140708121213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step_inherited_resources 
            ADD resource_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C7E87ECC89329D25 ON innova_step_inherited_resources (resource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step_inherited_resources 
            DROP COLUMN resource_id
        ");
        $this->addSql("
            ALTER TABLE innova_step_inherited_resources 
            DROP CONSTRAINT FK_C7E87ECC89329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_C7E87ECC89329D25'
            ) 
            ALTER TABLE innova_step_inherited_resources 
            DROP CONSTRAINT IDX_C7E87ECC89329D25 ELSE 
            DROP INDEX IDX_C7E87ECC89329D25 ON innova_step_inherited_resources
        ");
    }
}