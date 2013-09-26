<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/26 09:17:31
 */
class Version20130926091730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2excludedResource (
                id SERIAL NOT NULL,
                step_id INT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4CBCF07C73B21E9C ON innova_step2excludedResource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4CBCF07CB87FAB32 ON innova_step2excludedResource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResource
            ADD CONSTRAINT FK_4CBCF07C73B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResource
            ADD CONSTRAINT FK_4CBCF07CB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2excludedResource
        ");
    }
}
