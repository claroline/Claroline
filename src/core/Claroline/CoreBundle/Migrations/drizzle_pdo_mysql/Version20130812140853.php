<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/12 02:08:55
 */
class Version20130812140853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FFA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FFA76ED395 ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node CHANGE user_id creator_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF61220EA6 ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node CHANGE creator_id user_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FFA76ED395 ON claro_resource_node (user_id)
        ");
    }
}