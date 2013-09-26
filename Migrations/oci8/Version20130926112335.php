<?php

namespace Icap\DropzoneBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/26 11:23:37
 */
class Version20130926112335 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_correction (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                drop_id NUMBER(10) DEFAULT NULL, 
                drop_zone_id NUMBER(10) NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL, 
                \"comment\" CLOB DEFAULT NULL, 
                valid NUMBER(1) NOT NULL, 
                start_date TIMESTAMP(0) NOT NULL, 
                last_open_date TIMESTAMP(0) NOT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL, 
                finished NUMBER(1) NOT NULL, 
                editable NUMBER(1) NOT NULL, 
                reporter NUMBER(1) NOT NULL, 
                reportComment CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_CORRECTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_CORRECTION ADD CONSTRAINT ICAP__DROPZONEBUNDLE_CORRECTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_CORRECTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_CORRECTION_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_CORRECTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_CORRECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_CORRECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_CORRECTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_CORRECTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A76ED395 ON icap__dropzonebundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F404D224760 ON icap__dropzonebundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A8C6E7BD ON icap__dropzonebundle_correction (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_criterion (
                id NUMBER(10) NOT NULL, 
                drop_zone_id NUMBER(10) NOT NULL, 
                instruction CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_CRITERION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_CRITERION ADD CONSTRAINT ICAP__DROPZONEBUNDLE_CRITERION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_CRITERION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_CRITERION_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_CRITERION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_CRITERION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_CRITERION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_CRITERION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_CRITERION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F94B3BA7A8C6E7BD ON icap__dropzonebundle_criterion (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_document (
                id NUMBER(10) NOT NULL, 
                resource_node_id NUMBER(10) DEFAULT NULL, 
                drop_id NUMBER(10) NOT NULL, 
                type VARCHAR2(255) NOT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_DOCUMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_DOCUMENT ADD CONSTRAINT ICAP__DROPZONEBUNDLE_DOCUMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_DOCUMENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_DOCUMENT_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_DOCUMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_DOCUMENT_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_DOCUMENT_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_DOCUMENT_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_DOCUMENT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_744084241BAD783F ON icap__dropzonebundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_744084244D224760 ON icap__dropzonebundle_document (drop_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_drop (
                id NUMBER(10) NOT NULL, 
                drop_zone_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                hidden_directory_id NUMBER(10) DEFAULT NULL, 
                drop_date TIMESTAMP(0) NOT NULL, 
                reported NUMBER(1) NOT NULL, 
                finished NUMBER(1) NOT NULL, 
                \"number\" NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_DROP' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_DROP ADD CONSTRAINT ICAP__DROPZONEBUNDLE_DROP_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_DROP_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_DROP_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_DROP FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_DROP_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_DROP_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_DROP_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_DROP_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A8C6E7BD ON icap__dropzonebundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A76ED395 ON icap__dropzonebundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3AD19BA65342CDF ON icap__dropzonebundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, \"number\")
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_dropzone (
                id NUMBER(10) NOT NULL, 
                hidden_directory_id NUMBER(10) DEFAULT NULL, 
                edition_state NUMBER(5) NOT NULL, 
                instruction CLOB DEFAULT NULL, 
                allow_workspace_resource NUMBER(1) NOT NULL, 
                allow_upload NUMBER(1) NOT NULL, 
                allow_url NUMBER(1) NOT NULL, 
                allow_rich_text NUMBER(1) NOT NULL, 
                peer_review NUMBER(1) NOT NULL, 
                expected_total_correction NUMBER(5) NOT NULL, 
                display_notation_to_learners NUMBER(1) NOT NULL, 
                display_notation_message_to_learners NUMBER(1) NOT NULL, 
                minimum_score_to_pass NUMBER(5) NOT NULL, 
                manual_planning NUMBER(1) NOT NULL, 
                manual_state VARCHAR2(255) NOT NULL, 
                start_allow_drop TIMESTAMP(0) DEFAULT NULL, 
                end_allow_drop TIMESTAMP(0) DEFAULT NULL, 
                start_review TIMESTAMP(0) DEFAULT NULL, 
                end_review TIMESTAMP(0) DEFAULT NULL, 
                allow_comment_in_correction NUMBER(1) NOT NULL, 
                total_criteria_column NUMBER(5) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_DROPZONE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_DROPZONE ADD CONSTRAINT ICAP__DROPZONEBUNDLE_DROPZONE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_DROPZONE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_DROPZONE_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_DROPZONE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_DROPZONE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_DROPZONE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_DROPZONE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_DROPZONE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON icap__dropzonebundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON icap__dropzonebundle_dropzone (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_grade (
                id NUMBER(10) NOT NULL, 
                criterion_id NUMBER(10) NOT NULL, 
                correction_id NUMBER(10) NOT NULL, 
                value NUMBER(5) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__DROPZONEBUNDLE_GRADE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__DROPZONEBUNDLE_GRADE ADD CONSTRAINT ICAP__DROPZONEBUNDLE_GRADE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__DROPZONEBUNDLE_GRADE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__DROPZONEBUNDLE_GRADE_AI_PK BEFORE INSERT ON ICAP__DROPZONEBUNDLE_GRADE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__DROPZONEBUNDLE_GRADE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__DROPZONEBUNDLE_GRADE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__DROPZONEBUNDLE_GRADE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__DROPZONEBUNDLE_GRADE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9397766307 ON icap__dropzonebundle_grade (criterion_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9394AE086B ON icap__dropzonebundle_grade (correction_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON icap__dropzonebundle_grade (criterion_id, correction_id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F404D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            ADD CONSTRAINT FK_F94B3BA7A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084241BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084244D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA65342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC235342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9397766307 FOREIGN KEY (criterion_id) 
            REFERENCES icap__dropzonebundle_criterion (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9394AE086B FOREIGN KEY (correction_id) 
            REFERENCES icap__dropzonebundle_correction (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP CONSTRAINT FK_B3C52D9394AE086B
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP CONSTRAINT FK_B3C52D9397766307
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP CONSTRAINT FK_CDA81F404D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            DROP CONSTRAINT FK_744084244D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP CONSTRAINT FK_CDA81F40A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            DROP CONSTRAINT FK_F94B3BA7A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            DROP CONSTRAINT FK_3AD19BA6A8C6E7BD
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_criterion
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_document
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_drop
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_grade
        ");
    }
}