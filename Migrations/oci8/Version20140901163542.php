<?php

namespace Claroline\SurveyBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/01 04:35:44
 */
class Version20140901163542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id NUMBER(10) NOT NULL, 
                description CLOB DEFAULT NULL, 
                published NUMBER(1) NOT NULL, 
                closed NUMBER(1) NOT NULL, 
                has_public_result NUMBER(1) NOT NULL, 
                allow_answer_edition NUMBER(1) NOT NULL, 
                start_date TIMESTAMP(0) DEFAULT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_RESOURCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_RESOURCE ADD CONSTRAINT CLARO_SURVEY_RESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_RESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_RESOURCE_AI_PK BEFORE INSERT ON CLARO_SURVEY_RESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_RESOURCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_RESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11B27D4BB87FAB32 ON claro_survey_resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question_answer (
                id NUMBER(10) NOT NULL, 
                question_answer_id NUMBER(10) NOT NULL, 
                answer_content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER ADD CONSTRAINT CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_OPEN_ENDED_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2616BBEA3E60C9C ON claro_survey_open_ended_question_answer (question_answer_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question_answer (
                id NUMBER(10) NOT NULL, 
                question_answer_id NUMBER(10) NOT NULL, 
                choice_id NUMBER(10) NOT NULL, 
                content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER ADD CONSTRAINT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_MULTIPLE_CHOICE_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_FDB8AF37A3E60C9C ON claro_survey_multiple_choice_question_answer (question_answer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FDB8AF37998666D1 ON claro_survey_multiple_choice_question_answer (choice_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_answer (
                id NUMBER(10) NOT NULL, 
                answer_survey_id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) NOT NULL, 
                answer_comment CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_QUESTION_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_QUESTION_ANSWER ADD CONSTRAINT CLARO_SURVEY_QUESTION_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_QUESTION_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_QUESTION_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_QUESTION_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_QUESTION_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_QUESTION_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_9F5D3C468E018F4B ON claro_survey_question_answer (answer_survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9F5D3C461E27F6BF ON claro_survey_question_answer (question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_answer (
                id NUMBER(10) NOT NULL, 
                survey_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                answer_date TIMESTAMP(0) NOT NULL, 
                nb_answers NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_ANSWER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_ANSWER ADD CONSTRAINT CLARO_SURVEY_ANSWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_ANSWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_ANSWER_AI_PK BEFORE INSERT ON CLARO_SURVEY_ANSWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_ANSWER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_ANSWER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_ANSWER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_DFEB5349B3FE509D ON claro_survey_answer (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DFEB5349A76ED395 ON claro_survey_answer (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id NUMBER(10) NOT NULL, 
                choice_question_id NUMBER(10) NOT NULL, 
                content CLOB NOT NULL, 
                other NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_CHOICE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_CHOICE ADD CONSTRAINT CLARO_SURVEY_CHOICE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_CHOICE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_CHOICE_AI_PK BEFORE INSERT ON CLARO_SURVEY_CHOICE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_CHOICE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_CHOICE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_CHOICE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_CHOICE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id NUMBER(10) NOT NULL, 
                survey_id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) NOT NULL, 
                question_order NUMBER(10) NOT NULL, 
                mandatory NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_QUESTION_RELATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_QUESTION_RELATION ADD CONSTRAINT CLARO_SURVEY_QUESTION_RELATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_QUESTION_RELATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_QUESTION_RELATION_AI_PK BEFORE INSERT ON CLARO_SURVEY_QUESTION_RELATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_QUESTION_RELATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_QUESTION_RELATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_QUESTION_RELATION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_QUESTION_RELATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA4B3FE509D ON claro_survey_question_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA41E27F6BF ON claro_survey_question_relation (question_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_survey_question_relation ON claro_survey_question_relation (survey_id, question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_model (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) NOT NULL, 
                title CLOB NOT NULL, 
                question_type VARCHAR2(255) NOT NULL, 
                details CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_QUESTION_MODEL' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_QUESTION_MODEL ADD CONSTRAINT CLARO_SURVEY_QUESTION_MODEL_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_QUESTION_MODEL_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_QUESTION_MODEL_AI_PK BEFORE INSERT ON CLARO_SURVEY_QUESTION_MODEL FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_QUESTION_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_QUESTION_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_QUESTION_MODEL_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_QUESTION_MODEL_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_88BF64F482D40A1F ON claro_survey_question_model (workspace_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_survey_question_model.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) NOT NULL, 
                title CLOB NOT NULL, 
                question CLOB NOT NULL, 
                question_type VARCHAR2(255) NOT NULL, 
                comment_allowed NUMBER(1) NOT NULL, 
                comment_label VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_QUESTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_QUESTION ADD CONSTRAINT CLARO_SURVEY_QUESTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_QUESTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_QUESTION_AI_PK BEFORE INSERT ON CLARO_SURVEY_QUESTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_QUESTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_QUESTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_QUESTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C01382D40A1F ON claro_survey_question (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) DEFAULT NULL, 
                horizontal NUMBER(1) NOT NULL, 
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
            CREATE UNIQUE INDEX UNIQ_388E4C251E27F6BF ON claro_survey_multiple_choice_question (question_id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_resource 
            ADD CONSTRAINT FK_11B27D4BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question_answer 
            ADD CONSTRAINT FK_F2616BBEA3E60C9C FOREIGN KEY (question_answer_id) 
            REFERENCES claro_survey_question_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            ADD CONSTRAINT FK_FDB8AF37A3E60C9C FOREIGN KEY (question_answer_id) 
            REFERENCES claro_survey_question_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            ADD CONSTRAINT FK_FDB8AF37998666D1 FOREIGN KEY (choice_id) 
            REFERENCES claro_survey_choice (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            ADD CONSTRAINT FK_9F5D3C468E018F4B FOREIGN KEY (answer_survey_id) 
            REFERENCES claro_survey_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            ADD CONSTRAINT FK_9F5D3C461E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_answer 
            ADD CONSTRAINT FK_DFEB5349B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_answer 
            ADD CONSTRAINT FK_DFEB5349A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            ADD CONSTRAINT FK_C49D43FEA46B3B4F FOREIGN KEY (choice_question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            ADD CONSTRAINT FK_953FEEA4B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            ADD CONSTRAINT FK_953FEEA41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_model 
            ADD CONSTRAINT FK_88BF64F482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question 
            ADD CONSTRAINT FK_1BD4C01382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            ADD CONSTRAINT FK_388E4C251E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_answer 
            DROP CONSTRAINT FK_DFEB5349B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP CONSTRAINT FK_953FEEA4B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question_answer 
            DROP CONSTRAINT FK_F2616BBEA3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP CONSTRAINT FK_FDB8AF37A3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP CONSTRAINT FK_9F5D3C468E018F4B
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP CONSTRAINT FK_FDB8AF37998666D1
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP CONSTRAINT FK_9F5D3C461E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP CONSTRAINT FK_953FEEA41E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP CONSTRAINT FK_388E4C251E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            DROP CONSTRAINT FK_C49D43FEA46B3B4F
        ");
        $this->addSql("
            DROP TABLE claro_survey_resource
        ");
        $this->addSql("
            DROP TABLE claro_survey_open_ended_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_model
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
    }
}