<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 05:34:19
 */
class Version20130807173418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP COLUMN mime_type
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
    }
}