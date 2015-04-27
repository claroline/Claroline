<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 11:24:02
 */
class Version20150427112359 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD COLUMN license VARCHAR(255) DEFAULT NULL 
            ADD COLUMN author VARCHAR(255) DEFAULT NULL 
            ADD COLUMN active SMALLINT NOT NULL 
            DROP COLUMN license_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF460F904B
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD COLUMN license_id INTEGER DEFAULT NULL 
            DROP COLUMN license 
            DROP COLUMN author 
            DROP COLUMN active
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