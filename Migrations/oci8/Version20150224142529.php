<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 02:25:30
 */
class Version20150224142529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD (
                type VARCHAR2(50) NOT NULL, 
                authors CLOB NOT NULL, 
                description CLOB DEFAULT NULL NULL, 
                targetDir CLOB NOT NULL, 
                basePath CLOB NOT NULL, 
                license CLOB NOT NULL, 
                isInstalled NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_bundle.authors IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_bundle.license IS '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP (
                type, authors, description, targetDir, 
                basePath, license, isInstalled
            )
        ");
    }
}