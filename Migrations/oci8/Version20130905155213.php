<?php

namespace UJM\ExoBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/05 03:52:14
 */
class Version20130905155213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_exercise_question (
                exercise_id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) NOT NULL, 
                ordre NUMBER(10) NOT NULL, 
                PRIMARY KEY(exercise_id, question_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DB79F240E934951A ON ujm_exercise_question (exercise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DB79F2401E27F6BF ON ujm_exercise_question (question_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_link_hint_paper (
                hint_id NUMBER(10) NOT NULL, 
                paper_id NUMBER(10) NOT NULL, 
                \"view\" NUMBER(1) NOT NULL, 
                PRIMARY KEY(hint_id, paper_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B76F00F9519161AB ON ujm_link_hint_paper (hint_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B76F00F9E6758861 ON ujm_link_hint_paper (paper_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_choice (
                id NUMBER(10) NOT NULL, 
                interaction_qcm_id NUMBER(10) DEFAULT NULL, 
                label CLOB NOT NULL, 
                ordre NUMBER(10) NOT NULL, 
                weight DOUBLE PRECISION DEFAULT NULL, 
                feedback CLOB DEFAULT NULL, 
                right_response NUMBER(1) DEFAULT NULL, 
                position_force NUMBER(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_CHOICE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_CHOICE ADD CONSTRAINT UJM_CHOICE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_CHOICE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_CHOICE_AI_PK BEFORE INSERT ON UJM_CHOICE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_CHOICE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_CHOICE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_CHOICE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_CHOICE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_D4BDFA959DBF539 ON ujm_choice (interaction_qcm_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_subscription (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                exercise_id NUMBER(10) DEFAULT NULL, 
                creator NUMBER(1) NOT NULL, 
                admin NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_SUBSCRIPTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_SUBSCRIPTION ADD CONSTRAINT UJM_SUBSCRIPTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_SUBSCRIPTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_SUBSCRIPTION_AI_PK BEFORE INSERT ON UJM_SUBSCRIPTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_SUBSCRIPTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_SUBSCRIPTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_SUBSCRIPTION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_SUBSCRIPTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A17BA225A76ED395 ON ujm_subscription (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A17BA225E934951A ON ujm_subscription (exercise_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_planning (
                id NUMBER(10) NOT NULL, 
                group_id NUMBER(10) DEFAULT NULL, 
                start_time TIMESTAMP(0) NOT NULL, 
                end_time TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_PLANNING' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_PLANNING ADD CONSTRAINT UJM_PLANNING_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_PLANNING_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_PLANNING_AI_PK BEFORE INSERT ON UJM_PLANNING FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_PLANNING_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_PLANNING_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_PLANNING_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_PLANNING_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_4D0E9FCFFE54D947 ON ujm_planning (group_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_category (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                value VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_CATEGORY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_CATEGORY ADD CONSTRAINT UJM_CATEGORY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_CATEGORY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_CATEGORY_AI_PK BEFORE INSERT ON UJM_CATEGORY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_CATEGORY_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_CATEGORY_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_CATEGORY_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_CATEGORY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_9FDB39F8A76ED395 ON ujm_category (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_type_qcm (
                id NUMBER(10) NOT NULL, 
                value VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_TYPE_QCM' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_TYPE_QCM ADD CONSTRAINT UJM_TYPE_QCM_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_TYPE_QCM_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_TYPE_QCM_AI_PK BEFORE INSERT ON UJM_TYPE_QCM FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_TYPE_QCM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_TYPE_QCM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_TYPE_QCM_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_TYPE_QCM_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE ujm_hole (
                id NUMBER(10) NOT NULL, 
                interaction_hole_id NUMBER(10) DEFAULT NULL, 
                \"size\" NUMBER(10) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                position NUMBER(10) NOT NULL, 
                orthography NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_HOLE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_HOLE ADD CONSTRAINT UJM_HOLE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_HOLE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_HOLE_AI_PK BEFORE INSERT ON UJM_HOLE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_HOLE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_HOLE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_HOLE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_HOLE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_E9F4F52575EBD64D ON ujm_hole (interaction_hole_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                label VARCHAR2(255) NOT NULL, 
                url VARCHAR2(255) NOT NULL, 
                type VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_DOCUMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_DOCUMENT ADD CONSTRAINT UJM_DOCUMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_DOCUMENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_DOCUMENT_AI_PK BEFORE INSERT ON UJM_DOCUMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_DOCUMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_DOCUMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_DOCUMENT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_DOCUMENT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_41FEAA4FA76ED395 ON ujm_document (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_hole (
                id NUMBER(10) NOT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                html CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_INTERACTION_HOLE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_INTERACTION_HOLE ADD CONSTRAINT UJM_INTERACTION_HOLE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_INTERACTION_HOLE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_INTERACTION_HOLE_AI_PK BEFORE INSERT ON UJM_INTERACTION_HOLE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_INTERACTION_HOLE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_INTERACTION_HOLE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_INTERACTION_HOLE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_INTERACTION_HOLE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7343FAC1886DEE8F ON ujm_interaction_hole (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_open (
                id NUMBER(10) NOT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                typeopenquestion_id NUMBER(10) DEFAULT NULL, 
                orthography_correct NUMBER(1) NOT NULL, 
                scoreMaxLongResp DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_INTERACTION_OPEN' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_INTERACTION_OPEN ADD CONSTRAINT UJM_INTERACTION_OPEN_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_INTERACTION_OPEN_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_INTERACTION_OPEN_AI_PK BEFORE INSERT ON UJM_INTERACTION_OPEN FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_INTERACTION_OPEN_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_INTERACTION_OPEN_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_INTERACTION_OPEN_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_INTERACTION_OPEN_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_BFFE44F4886DEE8F ON ujm_interaction_open (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BFFE44F46AFD3CF ON ujm_interaction_open (typeopenquestion_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_word_response (
                id NUMBER(10) NOT NULL, 
                interaction_open_id NUMBER(10) DEFAULT NULL, 
                hole_id NUMBER(10) DEFAULT NULL, 
                response VARCHAR2(255) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_WORD_RESPONSE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_WORD_RESPONSE ADD CONSTRAINT UJM_WORD_RESPONSE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_WORD_RESPONSE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_WORD_RESPONSE_AI_PK BEFORE INSERT ON UJM_WORD_RESPONSE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_WORD_RESPONSE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_WORD_RESPONSE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_WORD_RESPONSE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_WORD_RESPONSE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C598DDBDFD ON ujm_word_response (interaction_open_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C515ADE12C ON ujm_word_response (hole_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_response (
                id NUMBER(10) NOT NULL, 
                paper_id NUMBER(10) DEFAULT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                ip VARCHAR2(255) NOT NULL, 
                mark DOUBLE PRECISION NOT NULL, 
                nb_tries NUMBER(10) NOT NULL, 
                response CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_RESPONSE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_RESPONSE ADD CONSTRAINT UJM_RESPONSE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_RESPONSE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_RESPONSE_AI_PK BEFORE INSERT ON UJM_RESPONSE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_RESPONSE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_RESPONSE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_RESPONSE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_RESPONSE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A7EC2BC2E6758861 ON ujm_response (paper_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A7EC2BC2886DEE8F ON ujm_response (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_unit (
                id NUMBER(10) NOT NULL, 
                value VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_UNIT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_UNIT ADD CONSTRAINT UJM_UNIT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_UNIT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_UNIT_AI_PK BEFORE INSERT ON UJM_UNIT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_UNIT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_UNIT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_UNIT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_UNIT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE ujm_group (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_GROUP' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_GROUP ADD CONSTRAINT UJM_GROUP_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_GROUP_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_GROUP_AI_PK BEFORE INSERT ON UJM_GROUP FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_GROUP_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_GROUP_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_GROUP_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_GROUP_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE ujm_exercise (
                id NUMBER(10) NOT NULL, 
                title VARCHAR2(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                shuffle NUMBER(1) DEFAULT NULL, 
                nb_question NUMBER(10) NOT NULL, 
                date_create TIMESTAMP(0) NOT NULL, 
                duration NUMBER(10) NOT NULL, 
                nb_question_page NUMBER(10) NOT NULL, 
                doprint NUMBER(1) DEFAULT NULL, 
                max_attempts NUMBER(10) NOT NULL, 
                correction_mode VARCHAR2(255) NOT NULL, 
                date_correction TIMESTAMP(0) DEFAULT NULL, 
                mark_mode VARCHAR2(255) NOT NULL, 
                start_date TIMESTAMP(0) NOT NULL, 
                use_date_end NUMBER(1) DEFAULT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL, 
                disp_button_interrupt NUMBER(1) DEFAULT NULL, 
                lock_attempt NUMBER(1) DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_EXERCISE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_EXERCISE ADD CONSTRAINT UJM_EXERCISE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_EXERCISE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_EXERCISE_AI_PK BEFORE INSERT ON UJM_EXERCISE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_EXERCISE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_EXERCISE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_EXERCISE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_EXERCISE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_374DF525B87FAB32 ON ujm_exercise (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_exercise_group (
                exercise_id NUMBER(10) NOT NULL, 
                group_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(exercise_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_78179004E934951A ON ujm_exercise_group (exercise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_78179004FE54D947 ON ujm_exercise_group (group_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_lock_attempt (
                id NUMBER(10) NOT NULL, 
                paper_id NUMBER(10) DEFAULT NULL, 
                key_lock VARCHAR2(255) NOT NULL, 
                \"date\" DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_LOCK_ATTEMPT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_LOCK_ATTEMPT ADD CONSTRAINT UJM_LOCK_ATTEMPT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_LOCK_ATTEMPT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_LOCK_ATTEMPT_AI_PK BEFORE INSERT ON UJM_LOCK_ATTEMPT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_LOCK_ATTEMPT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_LOCK_ATTEMPT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_LOCK_ATTEMPT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_LOCK_ATTEMPT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_7A7CDF96E6758861 ON ujm_lock_attempt (paper_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_graphic (
                id NUMBER(10) NOT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                document_id NUMBER(10) DEFAULT NULL, 
                width NUMBER(10) NOT NULL, 
                height NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_INTERACTION_GRAPHIC' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_INTERACTION_GRAPHIC ADD CONSTRAINT UJM_INTERACTION_GRAPHIC_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_INTERACTION_GRAPHIC_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_INTERACTION_GRAPHIC_AI_PK BEFORE INSERT ON UJM_INTERACTION_GRAPHIC FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_INTERACTION_GRAPHIC_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_INTERACTION_GRAPHIC_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_INTERACTION_GRAPHIC_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_INTERACTION_GRAPHIC_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_9EBD442F886DEE8F ON ujm_interaction_graphic (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9EBD442FC33F7837 ON ujm_interaction_graphic (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_coords (
                id NUMBER(10) NOT NULL, 
                interaction_graphic_id NUMBER(10) DEFAULT NULL, 
                value VARCHAR2(255) NOT NULL, 
                shape VARCHAR2(255) NOT NULL, 
                color VARCHAR2(255) NOT NULL, 
                score_coords DOUBLE PRECISION NOT NULL, 
                \"size\" DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_COORDS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_COORDS ADD CONSTRAINT UJM_COORDS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_COORDS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_COORDS_AI_PK BEFORE INSERT ON UJM_COORDS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_COORDS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_COORDS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_COORDS_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_COORDS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_CD7B49827876D500 ON ujm_coords (interaction_graphic_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_qcm (
                id NUMBER(10) NOT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                type_qcm_id NUMBER(10) DEFAULT NULL, 
                shuffle NUMBER(1) DEFAULT NULL, 
                score_right_response DOUBLE PRECISION DEFAULT NULL, 
                score_false_response DOUBLE PRECISION DEFAULT NULL, 
                weight_response NUMBER(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_INTERACTION_QCM' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_INTERACTION_QCM ADD CONSTRAINT UJM_INTERACTION_QCM_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_INTERACTION_QCM_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_INTERACTION_QCM_AI_PK BEFORE INSERT ON UJM_INTERACTION_QCM FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_INTERACTION_QCM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_INTERACTION_QCM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_INTERACTION_QCM_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_INTERACTION_QCM_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_58C3D5A1886DEE8F ON ujm_interaction_qcm (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_58C3D5A1DCB52A9E ON ujm_interaction_qcm (type_qcm_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_question (
                id NUMBER(10) NOT NULL, 
                expertise_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                category_id NUMBER(10) DEFAULT NULL, 
                title VARCHAR2(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                date_create TIMESTAMP(0) NOT NULL, 
                date_modify TIMESTAMP(0) DEFAULT NULL, 
                locked NUMBER(1) DEFAULT NULL, 
                model NUMBER(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_QUESTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_QUESTION ADD CONSTRAINT UJM_QUESTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_QUESTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_QUESTION_AI_PK BEFORE INSERT ON UJM_QUESTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_QUESTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_QUESTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_QUESTION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_QUESTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F606977A76ED395 ON ujm_question (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F60697712469DE2 ON ujm_question (category_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document_question (
                question_id NUMBER(10) NOT NULL, 
                document_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(question_id, document_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_52D4A3F11E27F6BF ON ujm_document_question (question_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_52D4A3F1C33F7837 ON ujm_document_question (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction (
                id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) DEFAULT NULL, 
                type VARCHAR2(255) NOT NULL, 
                invite CLOB NOT NULL, 
                ordre NUMBER(10) DEFAULT NULL, 
                feedback CLOB DEFAULT NULL, 
                locked_expertise NUMBER(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_INTERACTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_INTERACTION ADD CONSTRAINT UJM_INTERACTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_INTERACTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_INTERACTION_AI_PK BEFORE INSERT ON UJM_INTERACTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_INTERACTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_INTERACTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_INTERACTION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_INTERACTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_E7D801641E27F6BF ON ujm_interaction (question_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document_interaction (
                interaction_id NUMBER(10) NOT NULL, 
                document_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(interaction_id, document_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FF792E7A886DEE8F ON ujm_document_interaction (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FF792E7AC33F7837 ON ujm_document_interaction (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_expertise (
                id NUMBER(10) NOT NULL, 
                title VARCHAR2(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                status VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_EXPERTISE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_EXPERTISE ADD CONSTRAINT UJM_EXPERTISE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_EXPERTISE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_EXPERTISE_AI_PK BEFORE INSERT ON UJM_EXPERTISE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_EXPERTISE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_EXPERTISE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_EXPERTISE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_EXPERTISE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE ujm_expertise_user (
                expertise_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(expertise_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F60D61B9D5B92F9 ON ujm_expertise_user (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F60D61BA76ED395 ON ujm_expertise_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_hint (
                id NUMBER(10) NOT NULL, 
                interaction_id NUMBER(10) DEFAULT NULL, 
                value VARCHAR2(255) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_HINT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_HINT ADD CONSTRAINT UJM_HINT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_HINT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_HINT_AI_PK BEFORE INSERT ON UJM_HINT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_HINT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_HINT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_HINT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_HINT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_B5FFCBE7886DEE8F ON ujm_hint (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_paper (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                exercise_id NUMBER(10) DEFAULT NULL, 
                num_paper NUMBER(10) NOT NULL, 
                \"start\" TIMESTAMP(0) NOT NULL, 
                end TIMESTAMP(0) DEFAULT NULL, 
                ordre_question CLOB DEFAULT NULL, 
                archive NUMBER(1) DEFAULT NULL, 
                date_archive DATE DEFAULT NULL, 
                interupt NUMBER(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_PAPER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_PAPER ADD CONSTRAINT UJM_PAPER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_PAPER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_PAPER_AI_PK BEFORE INSERT ON UJM_PAPER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_PAPER_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_PAPER_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_PAPER_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_PAPER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_82972E4BA76ED395 ON ujm_paper (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82972E4BE934951A ON ujm_paper (exercise_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_type_open_question (
                id NUMBER(10) NOT NULL, 
                value VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'UJM_TYPE_OPEN_QUESTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE UJM_TYPE_OPEN_QUESTION ADD CONSTRAINT UJM_TYPE_OPEN_QUESTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE UJM_TYPE_OPEN_QUESTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER UJM_TYPE_OPEN_QUESTION_AI_PK BEFORE INSERT ON UJM_TYPE_OPEN_QUESTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT UJM_TYPE_OPEN_QUESTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT UJM_TYPE_OPEN_QUESTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'UJM_TYPE_OPEN_QUESTION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT UJM_TYPE_OPEN_QUESTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE ujm_share (
                user_id NUMBER(10) NOT NULL, 
                question_id NUMBER(10) NOT NULL, 
                allowToModify NUMBER(1) NOT NULL, 
                PRIMARY KEY(user_id, question_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_238BD307A76ED395 ON ujm_share (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_238BD3071E27F6BF ON ujm_share (question_id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F240E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F2401E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_link_hint_paper 
            ADD CONSTRAINT FK_B76F00F9519161AB FOREIGN KEY (hint_id) 
            REFERENCES ujm_hint (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_link_hint_paper 
            ADD CONSTRAINT FK_B76F00F9E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_choice 
            ADD CONSTRAINT FK_D4BDFA959DBF539 FOREIGN KEY (interaction_qcm_id) 
            REFERENCES ujm_interaction_qcm (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            ADD CONSTRAINT FK_A17BA225A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            ADD CONSTRAINT FK_A17BA225E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_planning 
            ADD CONSTRAINT FK_4D0E9FCFFE54D947 FOREIGN KEY (group_id) 
            REFERENCES ujm_group (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_category 
            ADD CONSTRAINT FK_9FDB39F8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_hole 
            ADD CONSTRAINT FK_E9F4F52575EBD64D FOREIGN KEY (interaction_hole_id) 
            REFERENCES ujm_interaction_hole (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_document 
            ADD CONSTRAINT FK_41FEAA4FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC1886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F4886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F46AFD3CF FOREIGN KEY (typeopenquestion_id) 
            REFERENCES ujm_type_open_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            ADD CONSTRAINT FK_4E1930C598DDBDFD FOREIGN KEY (interaction_open_id) 
            REFERENCES ujm_interaction_open (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            ADD CONSTRAINT FK_4E1930C515ADE12C FOREIGN KEY (hole_id) 
            REFERENCES ujm_hole (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD CONSTRAINT FK_374DF525B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_group 
            ADD CONSTRAINT FK_78179004E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_group 
            ADD CONSTRAINT FK_78179004FE54D947 FOREIGN KEY (group_id) 
            REFERENCES ujm_group (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_lock_attempt 
            ADD CONSTRAINT FK_7A7CDF96E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442FC33F7837 FOREIGN KEY (document_id) 
            REFERENCES ujm_document (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_coords 
            ADD CONSTRAINT FK_CD7B49827876D500 FOREIGN KEY (interaction_graphic_id) 
            REFERENCES ujm_interaction_graphic (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A1886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A1DCB52A9E FOREIGN KEY (type_qcm_id) 
            REFERENCES ujm_type_qcm (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id) 
            REFERENCES ujm_expertise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F60697712469DE2 FOREIGN KEY (category_id) 
            REFERENCES ujm_category (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_document_question 
            ADD CONSTRAINT FK_52D4A3F11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_document_question 
            ADD CONSTRAINT FK_52D4A3F1C33F7837 FOREIGN KEY (document_id) 
            REFERENCES ujm_document (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction 
            ADD CONSTRAINT FK_E7D801641E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_document_interaction 
            ADD CONSTRAINT FK_FF792E7A886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_document_interaction 
            ADD CONSTRAINT FK_FF792E7AC33F7837 FOREIGN KEY (document_id) 
            REFERENCES ujm_document (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_expertise_user 
            ADD CONSTRAINT FK_F60D61B9D5B92F9 FOREIGN KEY (expertise_id) 
            REFERENCES ujm_expertise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_expertise_user 
            ADD CONSTRAINT FK_F60D61BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE7886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_paper 
            ADD CONSTRAINT FK_82972E4BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_paper 
            ADD CONSTRAINT FK_82972E4BE934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD307A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD3071E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question 
            DROP CONSTRAINT FK_2F60697712469DE2
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_qcm 
            DROP CONSTRAINT FK_58C3D5A1DCB52A9E
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            DROP CONSTRAINT FK_4E1930C515ADE12C
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_graphic 
            DROP CONSTRAINT FK_9EBD442FC33F7837
        ");
        $this->addSql("
            ALTER TABLE ujm_document_question 
            DROP CONSTRAINT FK_52D4A3F1C33F7837
        ");
        $this->addSql("
            ALTER TABLE ujm_document_interaction 
            DROP CONSTRAINT FK_FF792E7AC33F7837
        ");
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP CONSTRAINT FK_E9F4F52575EBD64D
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            DROP CONSTRAINT FK_4E1930C598DDBDFD
        ");
        $this->addSql("
            ALTER TABLE ujm_planning 
            DROP CONSTRAINT FK_4D0E9FCFFE54D947
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_group 
            DROP CONSTRAINT FK_78179004FE54D947
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP CONSTRAINT FK_DB79F240E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            DROP CONSTRAINT FK_A17BA225E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_group 
            DROP CONSTRAINT FK_78179004E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_paper 
            DROP CONSTRAINT FK_82972E4BE934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_coords 
            DROP CONSTRAINT FK_CD7B49827876D500
        ");
        $this->addSql("
            ALTER TABLE ujm_choice 
            DROP CONSTRAINT FK_D4BDFA959DBF539
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP CONSTRAINT FK_DB79F2401E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_document_question 
            DROP CONSTRAINT FK_52D4A3F11E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction 
            DROP CONSTRAINT FK_E7D801641E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_share 
            DROP CONSTRAINT FK_238BD3071E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_hole 
            DROP CONSTRAINT FK_7343FAC1886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_open 
            DROP CONSTRAINT FK_BFFE44F4886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_response 
            DROP CONSTRAINT FK_A7EC2BC2886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_graphic 
            DROP CONSTRAINT FK_9EBD442F886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_qcm 
            DROP CONSTRAINT FK_58C3D5A1886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_document_interaction 
            DROP CONSTRAINT FK_FF792E7A886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_hint 
            DROP CONSTRAINT FK_B5FFCBE7886DEE8F
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            DROP CONSTRAINT FK_2F6069779D5B92F9
        ");
        $this->addSql("
            ALTER TABLE ujm_expertise_user 
            DROP CONSTRAINT FK_F60D61B9D5B92F9
        ");
        $this->addSql("
            ALTER TABLE ujm_link_hint_paper 
            DROP CONSTRAINT FK_B76F00F9519161AB
        ");
        $this->addSql("
            ALTER TABLE ujm_link_hint_paper 
            DROP CONSTRAINT FK_B76F00F9E6758861
        ");
        $this->addSql("
            ALTER TABLE ujm_response 
            DROP CONSTRAINT FK_A7EC2BC2E6758861
        ");
        $this->addSql("
            ALTER TABLE ujm_lock_attempt 
            DROP CONSTRAINT FK_7A7CDF96E6758861
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_open 
            DROP CONSTRAINT FK_BFFE44F46AFD3CF
        ");
        $this->addSql("
            DROP TABLE ujm_exercise_question
        ");
        $this->addSql("
            DROP TABLE ujm_link_hint_paper
        ");
        $this->addSql("
            DROP TABLE ujm_choice
        ");
        $this->addSql("
            DROP TABLE ujm_subscription
        ");
        $this->addSql("
            DROP TABLE ujm_planning
        ");
        $this->addSql("
            DROP TABLE ujm_category
        ");
        $this->addSql("
            DROP TABLE ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_hole
        ");
        $this->addSql("
            DROP TABLE ujm_document
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_hole
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_open
        ");
        $this->addSql("
            DROP TABLE ujm_word_response
        ");
        $this->addSql("
            DROP TABLE ujm_response
        ");
        $this->addSql("
            DROP TABLE ujm_unit
        ");
        $this->addSql("
            DROP TABLE ujm_group
        ");
        $this->addSql("
            DROP TABLE ujm_exercise
        ");
        $this->addSql("
            DROP TABLE ujm_exercise_group
        ");
        $this->addSql("
            DROP TABLE ujm_lock_attempt
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_graphic
        ");
        $this->addSql("
            DROP TABLE ujm_coords
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_question
        ");
        $this->addSql("
            DROP TABLE ujm_document_question
        ");
        $this->addSql("
            DROP TABLE ujm_interaction
        ");
        $this->addSql("
            DROP TABLE ujm_document_interaction
        ");
        $this->addSql("
            DROP TABLE ujm_expertise
        ");
        $this->addSql("
            DROP TABLE ujm_expertise_user
        ");
        $this->addSql("
            DROP TABLE ujm_hint
        ");
        $this->addSql("
            DROP TABLE ujm_paper
        ");
        $this->addSql("
            DROP TABLE ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE ujm_share
        ");
    }
}