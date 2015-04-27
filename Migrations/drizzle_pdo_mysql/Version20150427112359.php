<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 11:24:03
 */
class Version20150427112359 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF460F904B ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license VARCHAR(255) DEFAULT NULL, 
            ADD author VARCHAR(255) DEFAULT NULL, 
            ADD active BOOLEAN DEFAULT 'true' NOT NULL, 
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