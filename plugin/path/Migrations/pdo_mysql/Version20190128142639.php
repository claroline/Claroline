<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/01/28 02:27:28
 */
class Version20190128142639 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD secondaryResourcesTarget VARCHAR(255) NOT NULL DEFAULT "_self"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP secondaryResourcesTarget
        ');
    }
}
