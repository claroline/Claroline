<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/24 06:33:53
 */
class Version20230224063340 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet
        ');
        $this->addSql('
            ALTER TABLE claro_facet 
            ADD icon VARCHAR(255) DEFAULT NULL,
            CHANGE `name` entity_name VARCHAR(255) NOT NULL, 
            CHANGE `position` entity_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD icon VARCHAR(255) DEFAULT NULL, 
            ADD description LONGTEXT DEFAULT NULL, 
            DROP isDefaultCollapsed, 
            DROP isEditable, 
            CHANGE `name` entity_name VARCHAR(255) NOT NULL, 
            CHANGE `position` entity_order INT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_facet
            DROP icon,  
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE entity_order `position` INT NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name)
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD isDefaultCollapsed TINYINT(1) NOT NULL, 
            ADD isEditable TINYINT(1) NOT NULL, 
            DROP icon, 
            DROP description, 
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE entity_order `position` INT NOT NULL
        ');
    }
}
