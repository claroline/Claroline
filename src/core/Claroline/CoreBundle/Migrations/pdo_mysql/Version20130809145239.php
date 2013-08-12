<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 02:52:40
 */
class Version20130809145239 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP INDEX IDX_5E7F4AB8B87FAB32, 
            ADD UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD target_id INT NOT NULL, 
            CHANGE resourceNode_id resourceNode_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP INDEX UNIQ_5E7F4AB8B87FAB32, 
            ADD INDEX IDX_5E7F4AB8B87FAB32 (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP target_id, 
            CHANGE resourceNode_id resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }
}