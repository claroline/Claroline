<?php

namespace Innova\CollecticielBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/12 10:23:19
 */
class Version20150212102316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_criterion (
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
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_CRITERION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_CRITERION ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_CRITERION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_CRITERION_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_CRITERION_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_CRITERION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_CRITERION_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_CRITERION_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_CRITERION_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_CRITERION_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_CC96E6A6A8C6E7BD ON innova_collecticielbundle_criterion (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_drop (
                id NUMBER(10) NOT NULL, 
                drop_zone_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                hidden_directory_id NUMBER(10) DEFAULT NULL NULL, 
                drop_date TIMESTAMP(0) NOT NULL, 
                reported NUMBER(1) NOT NULL, 
                finished NUMBER(1) NOT NULL, 
                \"number\" NUMBER(10) NOT NULL, 
                auto_closed_drop NUMBER(1) DEFAULT '0' NOT NULL, 
                unlocked_drop NUMBER(1) DEFAULT '0' NOT NULL, 
                unlocked_user NUMBER(1) DEFAULT '0' NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_DROP' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_DROP ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_DROP_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_DROP_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_DROP_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_DROP FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROP_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROP_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_DROP_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROP_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_71757239A8C6E7BD ON innova_collecticielbundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_71757239A76ED395 ON innova_collecticielbundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_717572395342CDF ON innova_collecticielbundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, \"number\")
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_grade (
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
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_GRADE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_GRADE ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_GRADE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_GRADE_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_GRADE_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_GRADE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_GRADE_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_GRADE_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_GRADE_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_GRADE_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_D33E07AF97766307 ON innova_collecticielbundle_grade (criterion_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D33E07AF94AE086B ON innova_collecticielbundle_grade (correction_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON innova_collecticielbundle_grade (criterion_id, correction_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_document (
                id NUMBER(10) NOT NULL, 
                resource_node_id NUMBER(10) DEFAULT NULL NULL, 
                drop_id NUMBER(10) NOT NULL, 
                type VARCHAR2(255) NOT NULL, 
                url VARCHAR2(255) DEFAULT NULL NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_DOCUMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_DOCUMENT ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_DOCUMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_DOCUMENT_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_DOCUMENT_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_DOCUMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DOCUMENT_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DOCUMENT_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_DOCUMENT_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_DOCUMENT_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_correction (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                drop_id NUMBER(10) DEFAULT NULL NULL, 
                drop_zone_id NUMBER(10) NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL NULL, 
                \"comment\" CLOB DEFAULT NULL NULL, 
                valid NUMBER(1) NOT NULL, 
                start_date TIMESTAMP(0) NOT NULL, 
                last_open_date TIMESTAMP(0) NOT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL NULL, 
                finished NUMBER(1) NOT NULL, 
                editable NUMBER(1) NOT NULL, 
                reporter NUMBER(1) NOT NULL, 
                reportComment CLOB DEFAULT NULL NULL, 
                correctionDenied NUMBER(1) NOT NULL, 
                correctionDeniedComment CLOB DEFAULT NULL NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_CORRECTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_CORRECTION ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_CORRECTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_CORRECTION_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_CORRECTION_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_CORRECTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_CORRECTION_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_CORRECTION_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_CORRECTION_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_CORRECTION_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20BA76ED395 ON innova_collecticielbundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20B4D224760 ON innova_collecticielbundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20BA8C6E7BD ON innova_collecticielbundle_correction (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_dropzone (
                id NUMBER(10) NOT NULL, 
                hidden_directory_id NUMBER(10) DEFAULT NULL NULL, 
                event_agenda_drop NUMBER(10) DEFAULT NULL NULL, 
                event_agenda_correction NUMBER(10) DEFAULT NULL NULL, 
                edition_state NUMBER(5) NOT NULL, 
                instruction CLOB DEFAULT NULL NULL, 
                correction_instruction CLOB DEFAULT NULL NULL, 
                success_message CLOB DEFAULT NULL NULL, 
                fail_message CLOB DEFAULT NULL NULL, 
                allow_workspace_resource NUMBER(1) NOT NULL, 
                allow_upload NUMBER(1) NOT NULL, 
                allow_url NUMBER(1) NOT NULL, 
                allow_rich_text NUMBER(1) NOT NULL, 
                peer_review NUMBER(1) NOT NULL, 
                expected_total_correction NUMBER(5) NOT NULL, 
                display_notation_to_learners NUMBER(1) NOT NULL, 
                display_notation_message_to_learners NUMBER(1) NOT NULL, 
                minimum_score_to_pass DOUBLE PRECISION NOT NULL, 
                manual_planning NUMBER(1) NOT NULL, 
                manual_state VARCHAR2(255) NOT NULL, 
                start_allow_drop TIMESTAMP(0) DEFAULT NULL NULL, 
                end_allow_drop TIMESTAMP(0) DEFAULT NULL NULL, 
                start_review TIMESTAMP(0) DEFAULT NULL NULL, 
                end_review TIMESTAMP(0) DEFAULT NULL NULL, 
                allow_comment_in_correction NUMBER(1) NOT NULL, 
                force_comment_in_correction NUMBER(1) NOT NULL, 
                diplay_corrections_to_learners NUMBER(1) NOT NULL, 
                allow_correction_deny NUMBER(1) NOT NULL, 
                total_criteria_column NUMBER(5) NOT NULL, 
                auto_close_opened_drops_when_time_is_up NUMBER(1) DEFAULT '0' NOT NULL, 
                auto_close_state VARCHAR2(255) DEFAULT 'waiting' NOT NULL, 
                notify_on_drop NUMBER(1) DEFAULT '0' NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_DROPZONE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_DROPZONE ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_DROPZONE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_DROPZONE_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_DROPZONE_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_DROPZONE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROPZONE_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROPZONE_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_DROPZONE_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_DROPZONE_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070B5342CDF ON innova_collecticielbundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070BE6B974D2 ON innova_collecticielbundle_dropzone (event_agenda_drop)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070B8D9E1321 ON innova_collecticielbundle_dropzone (event_agenda_correction)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070BB87FAB32 ON innova_collecticielbundle_dropzone (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_criterion 
            ADD CONSTRAINT FK_CC96E6A6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_717572395342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF97766307 FOREIGN KEY (criterion_id) 
            REFERENCES innova_collecticielbundle_criterion (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF94AE086B FOREIGN KEY (correction_id) 
            REFERENCES innova_collecticielbundle_correction (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20B4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B5342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BE6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B8D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_grade 
            DROP CONSTRAINT FK_D33E07AF97766307
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            DROP CONSTRAINT FK_1C357F0C4D224760
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_correction 
            DROP CONSTRAINT FK_BA9AF20B4D224760
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_grade 
            DROP CONSTRAINT FK_D33E07AF94AE086B
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_criterion 
            DROP CONSTRAINT FK_CC96E6A6A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            DROP CONSTRAINT FK_71757239A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_correction 
            DROP CONSTRAINT FK_BA9AF20BA8C6E7BD
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_criterion
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_drop
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_grade
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_correction
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_dropzone
        ");
    }
}