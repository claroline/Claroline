<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/10/23 10:22:30
 */
final class Version20251023102228 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_authentication_oauth (
                serviceProvider VARCHAR(255) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL,
                clientId VARCHAR(255) NOT NULL, 
                clientSecret VARCHAR(255) NOT NULL, 
                urlAuthorize VARCHAR(255) DEFAULT NULL, 
                urlAccessToken VARCHAR(255) DEFAULT NULL, 
                urlResourceOwnerDetails VARCHAR(255) DEFAULT NULL, 
                additionalParameters JSON DEFAULT NULL, 
                fieldsMapping JSON DEFAULT NULL, 
                createOnLogin TINYINT(1) NOT NULL, 
                reactivateOnLogin TINYINT(1) NOT NULL, 
                buttonDisplayed TINYINT(1) NOT NULL, 
                buttonIcon VARCHAR(255) DEFAULT NULL, 
                buttonLabel VARCHAR(255) DEFAULT NULL, 
                buttonConfirm VARCHAR(255) DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                is_disabled TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_F4CAD80CD17F50A6 (uuid), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_authentication_oauth_organization (
                oauthclient_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_11EA32B1757AF5D (oauthclient_id), 
                INDEX IDX_11EA32B32C8A3DE (organization_id), 
                PRIMARY KEY (oauthclient_id, organization_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_authentication_oauth_organization 
            ADD CONSTRAINT FK_11EA32B1757AF5D FOREIGN KEY (oauthclient_id) 
            REFERENCES claro_authentication_oauth (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_authentication_oauth_organization 
            ADD CONSTRAINT FK_11EA32B32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_oauth_organization 
            DROP FOREIGN KEY FK_11EA32B1757AF5D
        ');
        $this->addSql('
            ALTER TABLE claro_authentication_oauth_organization 
            DROP FOREIGN KEY FK_11EA32B32C8A3DE
        ');
        $this->addSql('
            DROP TABLE claro_authentication_oauth
        ');
        $this->addSql('
            DROP TABLE claro_authentication_oauth_organization
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
