<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/17 08:59:50
 */
final class Version20250917085948 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD anonymized TINYINT(1) DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_evaluation 
            ADD anonymized TINYINT(1) DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD anonymized TINYINT(1) DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_evaluation 
            DROP anonymized
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP anonymized
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP anonymized
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
