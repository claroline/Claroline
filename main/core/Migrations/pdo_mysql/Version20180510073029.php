<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2018/05/10 07:30:32
 */
class Version20180510073029 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            CHANGE guid uuid VARCHAR(36) NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFD17F50A6 ON claro_resource_node (uuid)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node CHANGE creator_id creator_id INT DEFAULT NULL, 
            CHANGE hidden hidden TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node CHANGE creator_id creator_id INT NOT NULL, 
            CHANGE hidden hidden TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            CHANGE uuid guid VARCHAR(36) NOT NULL
        ");
    }
}