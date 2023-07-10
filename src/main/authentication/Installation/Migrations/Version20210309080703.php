<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/11 06:49:27
 */
final class Version20210309080703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_api_token (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                is_locked TINYINT(1) DEFAULT 0 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2F3470B75F37A13B (token), 
                UNIQUE INDEX UNIQ_2F3470B7D17F50A6 (uuid), 
                INDEX IDX_2F3470B7A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_ip_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ip VARCHAR(255) NOT NULL, 
                is_range TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) DEFAULT 0 NOT NULL, 
                UNIQUE INDEX UNIQ_FEB73761A5E3B32D (ip), 
                INDEX IDX_FEB73761A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_oauth_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                oauthId VARCHAR(255) NOT NULL, 
                service VARCHAR(255) NOT NULL, 
                INDEX IDX_E4539E82A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_api_token 
            ADD CONSTRAINT FK_2F3470B7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user 
            ADD CONSTRAINT FK_FEB73761A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_oauth_user 
            ADD CONSTRAINT FK_E4539E82A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_api_token 
            DROP FOREIGN KEY FK_2F3470B7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user 
            DROP FOREIGN KEY FK_FEB73761A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_oauth_user 
            DROP FOREIGN KEY FK_E4539E82A76ED395
        ');
        $this->addSql('
            DROP TABLE claro_api_token
        ');
        $this->addSql('
            DROP TABLE claro_ip_user
        ');
        $this->addSql('
            DROP TABLE claro_oauth_user
        ');
    }
}
