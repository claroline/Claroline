<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 11:03:49
 */
class Version20150415110347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD author VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD active BOOLEAN DEFAULT 'true' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP license_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP license
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP author
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP active
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
    }
}