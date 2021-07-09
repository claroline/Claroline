<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/08 08:39:47
 */
class Version20210408083942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL
        ');

        // initializes plannings for all Workspaces
        $this->addSql('
            INSERT INTO claro_planning (uuid, objectId, objectClass)
                SELECT UUID() AS uuid, w.uuid, "Claroline\\CoreBundle\\Entity\\Workspace\\Workspace" AS objectClass
                FROM claro_workspace AS w 
        ');

        // initializes planning for all Users
        $this->addSql('
            INSERT INTO claro_planning (uuid, objectId, objectClass)
                SELECT UUID() AS uuid, u.uuid, "Claroline\\CoreBundle\\Entity\\User" AS objectClass
                FROM claro_user AS u 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP createdAt, 
            DROP updatedAt
        ');
    }
}
