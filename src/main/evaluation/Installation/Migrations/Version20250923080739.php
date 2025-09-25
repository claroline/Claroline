<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/23 08:07:42
 */
final class Version20250923080739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD archivedAt DATETIME DEFAULT NULL, 
            ADD archived TINYINT(1) NOT NULL
        ');
        $this->addSql('UPDATE claro_resource_user_evaluation SET archived = 0');

        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_evaluation 
            ADD archivedAt DATETIME DEFAULT NULL, 
            ADD archived TINYINT(1) NOT NULL
        ');
        $this->addSql('UPDATE claro_evaluation_sequence_evaluation SET archived = 0');

        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD archivedAt DATETIME DEFAULT NULL, 
            ADD archived TINYINT(1) NOT NULL
        ');
        $this->addSql('UPDATE claro_workspace_evaluation SET archived = 0');

        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD archivedAt DATETIME DEFAULT NULL, 
            ADD archived TINYINT(1) NOT NULL
        ');
        $this->addSql('UPDATE claro_resource_evaluation SET archived = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP archivedAt, 
            DROP archived
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP archivedAt, 
            DROP archived
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
