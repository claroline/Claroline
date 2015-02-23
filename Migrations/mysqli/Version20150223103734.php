<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 10:37:36
 */
class Version20150223103734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD type VARCHAR(50) NOT NULL, 
            ADD authors LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD license LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP type, 
            DROP authors, 
            DROP description, 
            DROP license
        ");
    }
}