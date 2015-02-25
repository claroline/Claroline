<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

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
            ADD authors LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD targetDir LONGTEXT NOT NULL, 
            ADD basePath LONGTEXT NOT NULL, 
            ADD license LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
            ADD isInstalled TINYINT(1) NOT NULL
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