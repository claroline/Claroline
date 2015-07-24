<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/17 02:07:31
 */
class Version20150717140730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_presencebundle_presence (
                id INT AUTO_INCREMENT NOT NULL, 
                user_teacher_id INT DEFAULT NULL, 
                user_student_id INT DEFAULT NULL, 
                period_id INT DEFAULT NULL, 
                group_id INT DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                INDEX IDX_33952B61E6E7B8F1 (user_teacher_id), 
                INDEX IDX_33952B616CF389F6 (user_student_id), 
                INDEX IDX_33952B61EC8B7ADE (period_id), 
                INDEX IDX_33952B61FE54D947 (group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_presencebundle_period (
                id INT AUTO_INCREMENT NOT NULL, 
                num_period VARCHAR(255) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                school_day VARCHAR(255) NOT NULL, 
                begin_hour TIME NOT NULL, 
                end_hour TIME NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61E6E7B8F1 FOREIGN KEY (user_teacher_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B616CF389F6 FOREIGN KEY (user_student_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61EC8B7ADE FOREIGN KEY (period_id) 
            REFERENCES formalibre_presencebundle_period (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B61EC8B7ADE
        ");
        $this->addSql("
            DROP TABLE formalibre_presencebundle_presence
        ");
        $this->addSql("
            DROP TABLE formalibre_presencebundle_period
        ");
    }
}