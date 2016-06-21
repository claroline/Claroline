<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/10 08:24:34
 */
class Version20160210202433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE user_organization (
                user_id INT NOT NULL,
                organization_id INT NOT NULL,
                INDEX IDX_41221F7EA76ED395 (user_id),
                INDEX IDX_41221F7E32C8A3DE (organization_id),
                PRIMARY KEY(user_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro__event (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                time_slot_id INT NOT NULL,
                type VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT NOT NULL,
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)',
                INDEX IDX_42A594A4A76ED395 (user_id),
                INDEX IDX_42A594A482D40A1F (workspace_id),
                INDEX IDX_42A594A4D62B0FA (time_slot_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro__organization (
                id INT AUTO_INCREMENT NOT NULL,
                parent_id INT DEFAULT NULL,
                position INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) DEFAULT NULL,
                lft INT NOT NULL,
                lvl INT NOT NULL,
                rgt INT NOT NULL,
                root INT DEFAULT NULL,
                INDEX IDX_B68DD0D5727ACA70 (parent_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_organization (
                organization_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_9F29A0F732C8A3DE (organization_id),
                INDEX IDX_9F29A0F7A76ED395 (user_id),
                PRIMARY KEY(organization_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_administrator (
                organization_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_22EB9B3A32C8A3DE (organization_id),
                INDEX IDX_22EB9B3AA76ED395 (user_id),
                PRIMARY KEY(organization_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__year (
                id INT AUTO_INCREMENT NOT NULL,
                organization_id INT NOT NULL,
                start DATETIME NOT NULL,
                end DATETIME NOT NULL,
                openHour VARCHAR(255) NOT NULL,
                closeHour VARCHAR(255) NOT NULL,
                INDEX IDX_6CA4D43E32C8A3DE (organization_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__period (
                id INT AUTO_INCREMENT NOT NULL,
                year_id INT NOT NULL,
                start DATETIME NOT NULL,
                end DATETIME NOT NULL,
                description LONGTEXT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                INDEX IDX_5CC844EA40C1FEA7 (year_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__leave (
                id INT AUTO_INCREMENT NOT NULL,
                year_id INT NOT NULL,
                date DATETIME NOT NULL,
                INDEX IDX_E2BB1ED340C1FEA7 (year_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__time_slot (
                id INT AUTO_INCREMENT NOT NULL,
                organization_id INT NOT NULL,
                period_id INT NOT NULL,
                start DATETIME NOT NULL,
                end DATETIME NOT NULL,
                description LONGTEXT NOT NULL,
                baseTemplateName VARCHAR(255) NOT NULL,
                INDEX IDX_2EA6030D32C8A3DE (organization_id),
                INDEX IDX_2EA6030DEC8B7ADE (period_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__schedule_template (
                id INT AUTO_INCREMENT NOT NULL,
                start DATETIME NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                startHour VARCHAR(255) NOT NULL,
                endHour VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__location (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                street VARCHAR(255) NOT NULL,
                streetNumber VARCHAR(255) NOT NULL,
                boxNumber VARCHAR(255) DEFAULT NULL,
                pc VARCHAR(255) NOT NULL,
                town VARCHAR(255) NOT NULL,
                country VARCHAR(255) NOT NULL,
                latitude DOUBLE PRECISION DEFAULT NULL,
                longitude DOUBLE PRECISION DEFAULT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                type INT NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_location (
                location_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_932BBCCB64D218E (location_id),
                INDEX IDX_932BBCCBA76ED395 (user_id),
                PRIMARY KEY(location_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE location_organization (
                location_id INT NOT NULL,
                organization_id INT NOT NULL,
                INDEX IDX_B049331264D218E (location_id),
                INDEX IDX_B049331232C8A3DE (organization_id),
                PRIMARY KEY(location_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__event
            ADD CONSTRAINT FK_42A594A4A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__event
            ADD CONSTRAINT FK_42A594A482D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__event
            ADD CONSTRAINT FK_42A594A4D62B0FA FOREIGN KEY (time_slot_id)
            REFERENCES claro__time_slot (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__organization
            ADD CONSTRAINT FK_B68DD0D5727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_organization
            ADD CONSTRAINT FK_9F29A0F732C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_organization
            ADD CONSTRAINT FK_9F29A0F7A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator
            ADD CONSTRAINT FK_22EB9B3A32C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator
            ADD CONSTRAINT FK_22EB9B3AA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__year
            ADD CONSTRAINT FK_6CA4D43E32C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__period
            ADD CONSTRAINT FK_5CC844EA40C1FEA7 FOREIGN KEY (year_id)
            REFERENCES claro__year (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__leave
            ADD CONSTRAINT FK_E2BB1ED340C1FEA7 FOREIGN KEY (year_id)
            REFERENCES claro__year (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__time_slot
            ADD CONSTRAINT FK_2EA6030D32C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__time_slot
            ADD CONSTRAINT FK_2EA6030DEC8B7ADE FOREIGN KEY (period_id)
            REFERENCES claro__period (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_location
            ADD CONSTRAINT FK_932BBCCB64D218E FOREIGN KEY (location_id)
            REFERENCES claro__location (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_location
            ADD CONSTRAINT FK_932BBCCBA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE location_organization
            ADD CONSTRAINT FK_B049331264D218E FOREIGN KEY (location_id)
            REFERENCES claro__location (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE location_organization
            ADD CONSTRAINT FK_B049331232C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7E32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro__organization
            DROP FOREIGN KEY FK_B68DD0D5727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_user_organization
            DROP FOREIGN KEY FK_9F29A0F732C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator
            DROP FOREIGN KEY FK_22EB9B3A32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro__year
            DROP FOREIGN KEY FK_6CA4D43E32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro__time_slot
            DROP FOREIGN KEY FK_2EA6030D32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE location_organization
            DROP FOREIGN KEY FK_B049331232C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro__period
            DROP FOREIGN KEY FK_5CC844EA40C1FEA7
        ');
        $this->addSql('
            ALTER TABLE claro__leave
            DROP FOREIGN KEY FK_E2BB1ED340C1FEA7
        ');
        $this->addSql('
            ALTER TABLE claro__time_slot
            DROP FOREIGN KEY FK_2EA6030DEC8B7ADE
        ');
        $this->addSql('
            ALTER TABLE claro__event
            DROP FOREIGN KEY FK_42A594A4D62B0FA
        ');
        $this->addSql('
            ALTER TABLE claro_user_location
            DROP FOREIGN KEY FK_932BBCCB64D218E
        ');
        $this->addSql('
            ALTER TABLE location_organization
            DROP FOREIGN KEY FK_B049331264D218E
        ');
        $this->addSql('
            DROP TABLE user_organization
        ');
        $this->addSql('
            DROP TABLE claro__event
        ');
        $this->addSql('
            DROP TABLE claro__organization
        ');
        $this->addSql('
            DROP TABLE claro_user_organization
        ');
        $this->addSql('
            DROP TABLE claro_user_administrator
        ');
        $this->addSql('
            DROP TABLE claro__year
        ');
        $this->addSql('
            DROP TABLE claro__period
        ');
        $this->addSql('
            DROP TABLE claro__leave
        ');
        $this->addSql('
            DROP TABLE claro__time_slot
        ');
        $this->addSql('
            DROP TABLE claro__schedule_template
        ');
        $this->addSql('
            DROP TABLE claro__location
        ');
        $this->addSql('
            DROP TABLE claro_user_location
        ');
        $this->addSql('
            DROP TABLE location_organization
        ');
    }
}
