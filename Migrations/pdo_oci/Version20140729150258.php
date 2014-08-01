<?php

namespace Claroline\SurveyBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/29 03:02:59
 */
class Version20140729150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_answer (
                id NUMBER(10) NOT NULL, 
                respondent_id NUMBER(10) DEFAULT NULL, 
                survey_id NUMBER(10) NOT NULL, 
                answer_date TIMESTAMP(0) NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_OPEN_ENDED_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_OPEN_ENDED_ANSWER ADD CONSTRAINT CLARO_SURVEY_OPEN_ENDED_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_OPEN_ENDED_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_OPEN_ENDED_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_OPEN_ENDED_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_OPEN_ENDED_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_OPEN_ENDED_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_OPEN_ENDED_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_OPEN_ENDED_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B9DDE645CE80CD19 ON claro_survey_open_ended_answer (respondent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B9DDE645B3FE509D ON claro_survey_open_ended_answer (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question (
                id NUMBER(10) NOT NULL, 
                survey_id NUMBER(10) DEFAULT NULL, 
                body CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_OPEN_ENDED_QUESTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_OPEN_ENDED_QUESTION ADD CONSTRAINT CLARO_SURVEY_OPEN_ENDED_QUESTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_OPEN_ENDED_QUESTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_OPEN_ENDED_QUESTION_AI_PK BEFORE INSERT ON CLARO_SURVEY_OPEN_ENDED_QUESTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_OPEN_ENDED_QUESTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C6AAE2AB3FE509D ON claro_survey_open_ended_question (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id NUMBER(10) NOT NULL, 
                question_type VARCHAR2(255) NOT NULL, 
                isPublished NUMBER(1) NOT NULL, 
                isClosed NUMBER(1) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY ADD CONSTRAINT CLARO_SURVEY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_AI_PK BEFORE INSERT ON CLARO_SURVEY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E6CE963B87FAB32 ON claro_survey (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_answer (
                id NUMBER(10) NOT NULL, 
                respondent_id NUMBER(10) DEFAULT NULL, 
                survey_id NUMBER(10) NOT NULL, 
                choice_id NUMBER(10) NOT NULL, 
                answer_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER ADD CONSTRAINT CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E7E7635ECE80CD19 ON claro_survey_multiple_choice_answer (respondent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635EB3FE509D ON claro_survey_multiple_choice_answer (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635E998666D1 ON claro_survey_multiple_choice_answer (choice_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id NUMBER(10) NOT NULL, 
                survey_id NUMBER(10) DEFAULT NULL, 
                body CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION ADD CONSTRAINT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_AI_PK BEFORE INSERT ON CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_388E4C25B3FE509D ON claro_survey_multiple_choice_question (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_choice (
                id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE ADD CONSTRAINT CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_AI_PK BEFORE INSERT ON CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_CHOICE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_FC9173E91E27F6BF ON claro_survey_multiple_choice_choice (question_id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            ADD CONSTRAINT FK_B9DDE645CE80CD19 FOREIGN KEY (respondent_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            ADD CONSTRAINT FK_B9DDE645B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question 
            ADD CONSTRAINT FK_C6AAE2AB3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD CONSTRAINT FK_5E6CE963B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635ECE80CD19 FOREIGN KEY (respondent_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635EB3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635E998666D1 FOREIGN KEY (choice_id) 
            REFERENCES claro_survey_multiple_choice_choice (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            ADD CONSTRAINT FK_388E4C25B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_choice 
            ADD CONSTRAINT FK_FC9173E91E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            DROP CONSTRAINT FK_B9DDE645B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question 
            DROP CONSTRAINT FK_C6AAE2AB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP CONSTRAINT FK_E7E7635EB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP CONSTRAINT FK_388E4C25B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_choice 
            DROP CONSTRAINT FK_FC9173E91E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP CONSTRAINT FK_E7E7635E998666D1
        ");
        $this->addSql("
            DROP TABLE claro_survey_open_ended_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_open_ended_question
        ");
        $this->addSql("
            DROP TABLE claro_survey
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_choice
        ");
    }
}