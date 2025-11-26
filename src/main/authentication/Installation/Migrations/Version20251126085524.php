<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/11/26 08:55:26
 */
final class Version20251126085524 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_oauth 
            ADD organization_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_authentication_oauth 
            ADD CONSTRAINT FK_F4CAD80C32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_F4CAD80C32C8A3DE ON claro_authentication_oauth (organization_id)
        ');

        $this->addSql('
            DROP TABLE claro_authentication_oauth_organization
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_oauth 
            DROP FOREIGN KEY FK_F4CAD80C32C8A3DE
        ');
        $this->addSql('
            DROP INDEX IDX_F4CAD80C32C8A3DE ON claro_authentication_oauth
        ');
        $this->addSql('
            ALTER TABLE claro_authentication_oauth 
            DROP organization_id
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
