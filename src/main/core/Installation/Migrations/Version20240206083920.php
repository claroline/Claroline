<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/06 08:40:23
 */
final class Version20240206083920 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            CHANGE creation_date createdAt DATETIME DEFAULT NULL, 
            CHANGE modification_date updatedAt DATETIME DEFAULT NULL
        ');

        // we will keep the materializedPath in the DB till 14.3, in case there are some problems
        // it would be really costly to try to recompute it
        // DROP materializedPath
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            CHANGE createdAt creation_date DATETIME NOT NULL, 
            CHANGE updatedAt modification_date DATETIME NOT NULL
        ');

        // ADD materializedPath LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`
    }
}
