<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/11/23 06:07:01
 */
class Version20151123180700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro__organization (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                logo VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro__location (
                id INT AUTO_INCREMENT NOT NULL, 
                organization_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                street VARCHAR(255) NOT NULL, 
                pc VARCHAR(255) NOT NULL, 
                town VARCHAR(255) NOT NULL, 
                country VARCHAR(255) NOT NULL, 
                latitude DOUBLE PRECISION NOT NULL, 
                longitude DOUBLE PRECISION NOT NULL, 
                tel VARCHAR(255) NOT NULL, 
                INDEX IDX_24C849F732C8A3DE (organization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_location (
                location_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_932BBCCB64D218E (location_id), 
                INDEX IDX_932BBCCBA76ED395 (user_id), 
                PRIMARY KEY(location_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro__department (
                id INT AUTO_INCREMENT NOT NULL, 
                organization_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_258A209332C8A3DE (organization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_department (
                department_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_9BAEE81CAE80F5DF (department_id), 
                INDEX IDX_9BAEE81CA76ED395 (user_id), 
                PRIMARY KEY(department_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro__location 
            ADD CONSTRAINT FK_24C849F732C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_location 
            ADD CONSTRAINT FK_932BBCCB64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_location 
            ADD CONSTRAINT FK_932BBCCBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro__department 
            ADD CONSTRAINT FK_258A209332C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_department 
            ADD CONSTRAINT FK_9BAEE81CAE80F5DF FOREIGN KEY (department_id) 
            REFERENCES claro__department (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_department 
            ADD CONSTRAINT FK_9BAEE81CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro__location 
            DROP FOREIGN KEY FK_24C849F732C8A3DE
        ");
        $this->addSql("
            ALTER TABLE claro__department 
            DROP FOREIGN KEY FK_258A209332C8A3DE
        ");
        $this->addSql("
            ALTER TABLE claro_user_location 
            DROP FOREIGN KEY FK_932BBCCB64D218E
        ");
        $this->addSql("
            ALTER TABLE claro_user_department 
            DROP FOREIGN KEY FK_9BAEE81CAE80F5DF
        ");
        $this->addSql("
            DROP TABLE claro__organization
        ");
        $this->addSql("
            DROP TABLE claro__location
        ");
        $this->addSql("
            DROP TABLE claro_user_location
        ");
        $this->addSql("
            DROP TABLE claro__department
        ");
        $this->addSql("
            DROP TABLE claro_user_department
        ");
    }
}