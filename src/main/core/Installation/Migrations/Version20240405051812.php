<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/04/05 05:18:42
 */
final class Version20240405051812 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP disabled_notifications, 
            DROP showProgression
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP comments_activated, 
            DROP showTitle, 
            CHANGE creation_date creation_date DATETIME NOT NULL, 
            CHANGE modification_date modification_date DATETIME NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL, 
            ADD comments_activated TINYINT(1) NOT NULL, 
            ADD showTitle TINYINT(1) DEFAULT 1 NOT NULL, 
            CHANGE creation_date creation_date DATETIME DEFAULT NULL, 
            CHANGE modification_date modification_date DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD disabled_notifications TINYINT(1) NOT NULL, 
            ADD showProgression TINYINT(1) NOT NULL
        ');
    }
}
