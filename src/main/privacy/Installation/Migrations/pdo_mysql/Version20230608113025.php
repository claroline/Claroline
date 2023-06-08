<?php

namespace Claroline\PrivacyBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/08 11:30:41
 */
final class Version20230608113025 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_privacy_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                dpoName VARCHAR(255) DEFAULT NULL, 
                dpoEmail VARCHAR(255) DEFAULT NULL, 
                dpoPhone VARCHAR(255) DEFAULT NULL, 
                countryStorage VARCHAR(255) DEFAULT NULL, 
                termsOfService LONGTEXT DEFAULT NULL, 
                termsOfServiceEnabled TINYINT(1) NOT NULL, 
                address_street1 VARCHAR(255) DEFAULT NULL, 
                address_street2 VARCHAR(255) DEFAULT NULL, 
                address_postal_code VARCHAR(255) DEFAULT NULL, 
                address_city VARCHAR(255) DEFAULT NULL, 
                address_state VARCHAR(255) DEFAULT NULL, 
                address_country VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_privacy_parameters
        ');
    }
}
