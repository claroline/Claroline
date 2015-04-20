<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 11:03:50
 */
class Version20150415110347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX claro_resource_node.IDX_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license VARCHAR(255) DEFAULT NULL, 
            ADD author VARCHAR(255) DEFAULT NULL, 
            ADD active BIT DEFAULT '1' NOT NULL, 
            DROP license_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license_id INT DEFAULT NULL, 
            DROP license, 
            DROP author, 
            DROP active
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
    }
}