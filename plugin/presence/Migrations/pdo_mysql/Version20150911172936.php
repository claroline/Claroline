<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/11 05:29:38
 */
class Version20150911172936 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE formalibre_presencebundle_schoolYear (
                id INT AUTO_INCREMENT NOT NULL, 
                schoolYearName VARCHAR(255) NOT NULL, 
                schoolYear_begin DATE NOT NULL, 
                schoolYear_end DATE NOT NULL, 
                schoolDay_begin_hour TIME NOT NULL, 
                schoolDay_end_hour TIME NOT NULL, 
                schoolYearActual TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_presencebundle_status (
                id INT AUTO_INCREMENT NOT NULL, 
                statusName VARCHAR(255) DEFAULT NULL, 
                statusColor VARCHAR(255) DEFAULT NULL, 
                statusByDefault TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_presencebundle_presence (
                id INT AUTO_INCREMENT NOT NULL, 
                status_id INT DEFAULT NULL, 
                user_teacher_id INT DEFAULT NULL, 
                user_student_id INT DEFAULT NULL, 
                period_id INT DEFAULT NULL, 
                group_id INT DEFAULT NULL, 
                date DATE NOT NULL, 
                Comment VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_33952B616BF700BD (status_id), 
                INDEX IDX_33952B61E6E7B8F1 (user_teacher_id), 
                INDEX IDX_33952B616CF389F6 (user_student_id), 
                INDEX IDX_33952B61EC8B7ADE (period_id), 
                INDEX IDX_33952B61FE54D947 (group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_presencebundle_period (
                id INT AUTO_INCREMENT NOT NULL, 
                num_period VARCHAR(255) NOT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                school_date DATE NOT NULL, 
                school_day VARCHAR(255) NOT NULL, 
                begin_hour TIME NOT NULL, 
                end_hour TIME NOT NULL, 
                visibility TINYINT(1) NOT NULL, 
                schoolYear_id INT DEFAULT NULL, 
                INDEX IDX_4E4AE7C08BF32374 (schoolYear_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_presencebundle_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_8A92280DD60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B616BF700BD FOREIGN KEY (status_id) 
            REFERENCES formalibre_presencebundle_status (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61E6E7B8F1 FOREIGN KEY (user_teacher_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B616CF389F6 FOREIGN KEY (user_student_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61EC8B7ADE FOREIGN KEY (period_id) 
            REFERENCES formalibre_presencebundle_period (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_period 
            ADD CONSTRAINT FK_4E4AE7C08BF32374 FOREIGN KEY (schoolYear_id) 
            REFERENCES formalibre_presencebundle_schoolYear (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_rights 
            ADD CONSTRAINT FK_8A92280DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_period 
            DROP FOREIGN KEY FK_4E4AE7C08BF32374
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B616BF700BD
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B61EC8B7ADE
        ');
        $this->addSql('
            DROP TABLE formalibre_presencebundle_schoolYear
        ');
        $this->addSql('
            DROP TABLE formalibre_presencebundle_status
        ');
        $this->addSql('
            DROP TABLE formalibre_presencebundle_presence
        ');
        $this->addSql('
            DROP TABLE formalibre_presencebundle_period
        ');
        $this->addSql('
            DROP TABLE formalibre_presencebundle_rights
        ');
    }
}
