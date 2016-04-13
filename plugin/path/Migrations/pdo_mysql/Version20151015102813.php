<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/10/15 10:28:16
 */
class Version20151015102813 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            DROP FOREIGN KEY FK_C7E87ECC89329D25
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            DROP FOREIGN KEY FK_C7E87ECC89329D25
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ');
    }
}
