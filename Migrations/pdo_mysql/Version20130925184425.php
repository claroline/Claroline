<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 06:44:25
 */
class Version20130925184425 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE Step2ExcludedResource (
                id INT AUTO_INCREMENT NOT NULL,
                step_id INT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                INDEX IDX_C6F7A1E173B21E9C (step_id),
                INDEX IDX_C6F7A1E1B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E173B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E1B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE Step2ExcludedResource
        ");
    }
}
