<?php

namespace Claroline\PrivacyBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/06 11:24:35
 */
final class Version20230606112415 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_privacy_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                dpoName VARCHAR(255) DEFAULT NULL, 
                dpoEmail VARCHAR(255) DEFAULT NULL, 
                dpoPhone VARCHAR(255) DEFAULT NULL, 
                countryStorage VARCHAR(255) NOT NULL, 
                termsOfService LONGTEXT DEFAULT NULL, 
                isTermsOfServiceEnabled TINYINT(1) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                address_street1 VARCHAR(255) DEFAULT NULL, 
                address_street2 VARCHAR(255) DEFAULT NULL, 
                address_postal_code VARCHAR(255) DEFAULT NULL, 
                address_city VARCHAR(255) DEFAULT NULL, 
                address_state VARCHAR(255) DEFAULT NULL, 
                address_country VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5F8AD24DD17F50A6 (uuid), 
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
