<?php

namespace Claroline\PrivacyBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/12/21 11:10:45
 */
final class Version20231221111045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_privacy_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                template_id INT DEFAULT NULL, 
                country_storage VARCHAR(255) DEFAULT NULL, 
                dpo_name VARCHAR(255) DEFAULT NULL, 
                dpo_email VARCHAR(255) DEFAULT NULL, 
                dpo_phone VARCHAR(255) DEFAULT NULL, 
                dpo_address_street1 VARCHAR(255) DEFAULT NULL, 
                dpo_address_street2 VARCHAR(255) DEFAULT NULL, 
                dpo_address_postal_code VARCHAR(255) DEFAULT NULL, 
                dpo_address_city VARCHAR(255) DEFAULT NULL, 
                dpo_address_state VARCHAR(255) DEFAULT NULL, 
                dpo_address_country VARCHAR(255) DEFAULT NULL, 
                tos_enabled TINYINT(1) NOT NULL, 
                INDEX IDX_5F8AD24D5DA0FB8 (template_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_privacy_parameters 
            ADD CONSTRAINT FK_5F8AD24D5DA0FB8 FOREIGN KEY (template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_privacy_parameters 
            DROP FOREIGN KEY FK_5F8AD24D5DA0FB8
        ');
        $this->addSql('
            DROP TABLE claro_privacy_parameters
        ');
    }
}
