<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/30 01:08:02
 */
final class Version20250930130800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD contextId VARCHAR(255) DEFAULT NULL, 
            ADD objectClass VARCHAR(255) DEFAULT NULL, 
            ADD objectId VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_log_functionnal AS l
            LEFT JOIN claro_workspace AS w ON l.workspace_id = w.id
            SET l.contextId = w.uuid
            WHERE l.workspace_id IS NOT NULL
        ');

        $this->addSql('
            UPDATE claro_log_functionnal AS l
            LEFT JOIN claro_resource_node AS n ON l.resource_id = n.id
            SET l.objectId = n.uuid, l.objectClass = "Claroline\\\CoreBundle\\\Entity\\\Resource\\\ResourceNode"
            WHERE l.resource_id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY `FK_29C2B64E82D40A1F`
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY `FK_29C2B64E89329D25`
        ');
        $this->addSql('
            DROP INDEX IDX_29C2B64E82D40A1F ON claro_log_functionnal
        ');
        $this->addSql('
            DROP INDEX IDX_29C2B64E89329D25 ON claro_log_functionnal
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP resource_id, 
            DROP workspace_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD resource_id INT DEFAULT NULL, 
            ADD workspace_id INT DEFAULT NULL, 
            DROP contextId, 
            DROP objectClass, 
            DROP objectId
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD CONSTRAINT `FK_29C2B64E82D40A1F` FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) ON UPDATE NO ACTION 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD CONSTRAINT `FK_29C2B64E89329D25` FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) ON UPDATE NO ACTION 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_29C2B64E82D40A1F ON claro_log_functionnal (workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_29C2B64E89329D25 ON claro_log_functionnal (resource_id)
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
