<?php

namespace Claroline\PrivacyBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/05/31 12:44:02
 */
final class Version20230531124357 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_privacy_parameters CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT NULL, 
            CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT NULL, 
            CHANGE address_postal_code address_postal_code VARCHAR(255) DEFAULT NULL, 
            CHANGE address_city address_city VARCHAR(255) DEFAULT NULL, 
            CHANGE address_state address_state VARCHAR(255) DEFAULT NULL, 
            CHANGE address_country address_country VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_privacy_parameters CHANGE address_street1 address_street1 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`, 
            CHANGE address_street2 address_street2 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`, 
            CHANGE address_postal_code address_postal_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`, 
            CHANGE address_city address_city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`, 
            CHANGE address_state address_state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`, 
            CHANGE address_country address_country VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'NULL' COLLATE `utf8mb4_unicode_ci`
        ");
    }
}
