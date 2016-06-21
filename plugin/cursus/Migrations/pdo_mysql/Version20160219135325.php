<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/19 01:53:29
 */
class Version20160219135325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_organizations (
                cursus_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_3B65A4C840AEF4B9 (cursus_id), 
                INDEX IDX_3B65A4C832C8A3DE (organization_id), 
                PRIMARY KEY(cursus_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_validators (
                course_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_219067F2591CC992 (course_id), 
                INDEX IDX_219067F2A76ED395 (user_id), 
                PRIMARY KEY(course_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_validators (
                coursesession_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_7EE284A7AE020D6E (coursesession_id), 
                INDEX IDX_7EE284A7A76ED395 (user_id), 
                PRIMARY KEY(coursesession_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_organizations 
            ADD CONSTRAINT FK_3B65A4C840AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_organizations 
            ADD CONSTRAINT FK_3B65A4C832C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_validators 
            ADD CONSTRAINT FK_219067F2591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_validators 
            ADD CONSTRAINT FK_219067F2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_validators 
            ADD CONSTRAINT FK_7EE284A7AE020D6E FOREIGN KEY (coursesession_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_validators 
            ADD CONSTRAINT FK_7EE284A7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD validator_id INT DEFAULT NULL, 
            ADD organization_admin_id INT DEFAULT NULL, 
            ADD queue_status INT NOT NULL, 
            ADD validation_date DATETIME DEFAULT NULL, 
            ADD user_validation_date DATETIME DEFAULT NULL, 
            ADD validator_validation_date DATETIME DEFAULT NULL, 
            ADD organization_validation_date DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EB0644AEC FOREIGN KEY (validator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E8B3340B2 FOREIGN KEY (organization_admin_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_E068776EB0644AEC ON claro_cursusbundle_course_registration_queue (validator_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_E068776E8B3340B2 ON claro_cursusbundle_course_registration_queue (organization_admin_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD validator_id INT DEFAULT NULL, 
            ADD organization_admin_id INT DEFAULT NULL, 
            ADD queue_status INT NOT NULL, 
            ADD validation_date DATETIME DEFAULT NULL, 
            ADD user_validation_date DATETIME DEFAULT NULL, 
            ADD validator_validation_date DATETIME DEFAULT NULL, 
            ADD organization_validation_date DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296B0644AEC FOREIGN KEY (validator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC2968B3340B2 FOREIGN KEY (organization_admin_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_334FC296B0644AEC ON claro_cursusbundle_course_session_registration_queue (validator_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_334FC2968B3340B2 ON claro_cursusbundle_course_session_registration_queue (organization_admin_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD workspace_id INT DEFAULT NULL, 
            ADD user_validation TINYINT(1) NOT NULL, 
            ADD organization_validation TINYINT(1) NOT NULL, 
            ADD max_users INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D34982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D34982D40A1F ON claro_cursusbundle_course (workspace_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD user_validation TINYINT(1) NOT NULL, 
            ADD organization_validation TINYINT(1) NOT NULL, 
            ADD max_users INT DEFAULT NULL, 
            ADD session_type INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_organizations
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_validators
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_validators
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D34982D40A1F
        ');
        $this->addSql('
            DROP INDEX IDX_3359D34982D40A1F ON claro_cursusbundle_course
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP workspace_id, 
            DROP user_validation, 
            DROP organization_validation, 
            DROP max_users
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            DROP FOREIGN KEY FK_E068776EB0644AEC
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            DROP FOREIGN KEY FK_E068776E8B3340B2
        ');
        $this->addSql('
            DROP INDEX IDX_E068776EB0644AEC ON claro_cursusbundle_course_registration_queue
        ');
        $this->addSql('
            DROP INDEX IDX_E068776E8B3340B2 ON claro_cursusbundle_course_registration_queue
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            DROP validator_id, 
            DROP organization_admin_id, 
            DROP queue_status, 
            DROP validation_date, 
            DROP user_validation_date, 
            DROP validator_validation_date, 
            DROP organization_validation_date
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP user_validation, 
            DROP organization_validation, 
            DROP max_users, 
            DROP session_type
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP FOREIGN KEY FK_334FC296B0644AEC
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP FOREIGN KEY FK_334FC2968B3340B2
        ');
        $this->addSql('
            DROP INDEX IDX_334FC296B0644AEC ON claro_cursusbundle_course_session_registration_queue
        ');
        $this->addSql('
            DROP INDEX IDX_334FC2968B3340B2 ON claro_cursusbundle_course_session_registration_queue
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP validator_id, 
            DROP organization_admin_id, 
            DROP queue_status, 
            DROP validation_date, 
            DROP user_validation_date, 
            DROP validator_validation_date, 
            DROP organization_validation_date
        ');
    }
}
