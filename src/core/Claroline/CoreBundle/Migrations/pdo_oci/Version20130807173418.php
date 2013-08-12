<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP (mime_type)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            DROP (mime_type)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD (
                mime_type VARCHAR2(255) DEFAULT NULL
            )
        ");
    }
}