<?php

namespace FormaLibre\SupportBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/29 10:52:41
 */
class Version20150729105239 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE formalibre_support_status (
                id INT AUTO_INCREMENT NOT NULL, 
                status_name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                status_order INT NOT NULL, 
                status_type INT NOT NULL, 
                UNIQUE INDEX UNIQ_B509CF116625D392 (status_name), 
                UNIQUE INDEX UNIQ_B509CF1177153098 (code), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE formalibre_support_ticket (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                type_id INT DEFAULT NULL, 
                status_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT NOT NULL, 
                contact_mail VARCHAR(255) NOT NULL, 
                contact_phone VARCHAR(255) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                num INT NOT NULL, 
                level INT NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_59A907AEA76ED395 (user_id), 
                INDEX IDX_59A907AEC54C8C93 (type_id), 
                INDEX IDX_59A907AE6BF700BD (status_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE formalibre_support_type (
                id INT AUTO_INCREMENT NOT NULL, 
                type_name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_1FFFD8FB892CBB0E (type_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_support_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ticket_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                is_admin TINYINT(1) NOT NULL, 
                INDEX IDX_EA0B277BA76ED395 (user_id), 
                INDEX IDX_EA0B277B700047D2 (ticket_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_support_intervention (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ticket_id INT DEFAULT NULL, 
                status_id INT DEFAULT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                externalComment LONGTEXT DEFAULT NULL, 
                internalComment LONGTEXT DEFAULT NULL, 
                INDEX IDX_B1287482A76ED395 (user_id), 
                INDEX IDX_B1287482700047D2 (ticket_id), 
                INDEX IDX_B12874826BF700BD (status_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE formalibre_support_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            ADD CONSTRAINT FK_59A907AEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            ADD CONSTRAINT FK_59A907AEC54C8C93 FOREIGN KEY (type_id) 
            REFERENCES formalibre_support_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            ADD CONSTRAINT FK_59A907AE6BF700BD FOREIGN KEY (status_id) 
            REFERENCES formalibre_support_status (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_comment 
            ADD CONSTRAINT FK_EA0B277BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_comment 
            ADD CONSTRAINT FK_EA0B277B700047D2 FOREIGN KEY (ticket_id) 
            REFERENCES formalibre_support_ticket (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_intervention 
            ADD CONSTRAINT FK_B1287482A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_intervention 
            ADD CONSTRAINT FK_B1287482700047D2 FOREIGN KEY (ticket_id) 
            REFERENCES formalibre_support_ticket (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_intervention 
            ADD CONSTRAINT FK_B12874826BF700BD FOREIGN KEY (status_id) 
            REFERENCES formalibre_support_status (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            DROP FOREIGN KEY FK_59A907AE6BF700BD
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_intervention 
            DROP FOREIGN KEY FK_B12874826BF700BD
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_comment 
            DROP FOREIGN KEY FK_EA0B277B700047D2
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_intervention 
            DROP FOREIGN KEY FK_B1287482700047D2
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            DROP FOREIGN KEY FK_59A907AEC54C8C93
        ');
        $this->addSql('
            DROP TABLE formalibre_support_status
        ');
        $this->addSql('
            DROP TABLE formalibre_support_ticket
        ');
        $this->addSql('
            DROP TABLE formalibre_support_type
        ');
        $this->addSql('
            DROP TABLE formalibre_support_comment
        ');
        $this->addSql('
            DROP TABLE formalibre_support_intervention
        ');
        $this->addSql('
            DROP TABLE formalibre_support_configuration
        ');
    }
}
