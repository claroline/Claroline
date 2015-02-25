<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

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
            ADD type VARCHAR(50) NOT NULL, 
            ADD authors TEXT NOT NULL, 
            ADD description TEXT DEFAULT NULL, 
            ADD targetDir TEXT NOT NULL, 
            ADD basePath TEXT NOT NULL, 
            ADD license TEXT NOT NULL, 
            ADD isInstalled BIT NOT NULL
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
            DROP type, 
            DROP authors, 
            DROP description, 
            DROP targetDir, 
            DROP basePath, 
            DROP license, 
            DROP isInstalled
        ");
    }
}