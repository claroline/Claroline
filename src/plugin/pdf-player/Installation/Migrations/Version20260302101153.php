<?php

namespace Claroline\PdfPlayerBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/03/02 10:11:54
 */
final class Version20260302101153 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
        ALTER TABLE claro_pdf_resource 
        ADD scrollMode VARCHAR(255) NOT NULL DEFAULT 'PAGE'
    ");
        $this->addSql("
        UPDATE claro_pdf_resource 
        SET scrollMode = 'PAGE' 
        WHERE scrollMode IS NULL
    ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_pdf_resource 
            DROP scrollMode
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
