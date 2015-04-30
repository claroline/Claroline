<?php

namespace Claroline\CursusBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/29 11:32:21
 */
class Version20150429113219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_registration_queue (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                course_id NUMBER(10) NOT NULL, 
                application_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE ADD CONSTRAINT CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_AI_PK BEFORE INSERT ON CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_CURSUSBUNDLE_COURSE_REGISTRATION_QUEUE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776EA76ED395 ON claro_cursusbundle_course_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776E591CC992 ON claro_cursusbundle_course_registration_queue (course_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX course_queue_unique_course_user ON claro_cursusbundle_course_registration_queue (course_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD (
                icon VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD (
                icon VARCHAR2(255) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_registration_queue
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            DROP (icon)
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP (icon)
        ");
    }
}