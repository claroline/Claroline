<?php

namespace Claroline\ScormBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 05:09:11
 */
class Version20140716170910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco (
                id NUMBER(10) NOT NULL, 
                scorm_resource_id NUMBER(10) NOT NULL, 
                sco_parent_id NUMBER(10) DEFAULT NULL, 
                entry_url VARCHAR2(255) DEFAULT NULL, 
                scorm_identifier VARCHAR2(255) NOT NULL, 
                title VARCHAR2(200) NOT NULL, 
                visible NUMBER(1) NOT NULL, 
                parameters VARCHAR2(1000) DEFAULT NULL, 
                time_limit_action VARCHAR2(255) DEFAULT NULL, 
                launch_data VARCHAR2(4000) DEFAULT NULL, 
                is_block NUMBER(1) NOT NULL, 
                max_time_allowed VARCHAR2(255) DEFAULT NULL, 
                completion_threshold NUMERIC(10, 7) DEFAULT NULL, 
                scaled_passing_score NUMERIC(10, 7) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SCORM_2004_SCO' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_2004_SCO ADD CONSTRAINT CLARO_SCORM_2004_SCO_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_2004_SCO_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_2004_SCO_AI_PK BEFORE INSERT ON CLARO_SCORM_2004_SCO FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_2004_SCO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_2004_SCO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_2004_SCO_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_2004_SCO_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_E88F1DDD167AFF3D ON claro_scorm_2004_sco (scorm_resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E88F1DDD48C689D5 ON claro_scorm_2004_sco (sco_parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco_tracking (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                sco_id NUMBER(10) NOT NULL, 
                score_raw NUMBER(10) DEFAULT NULL, 
                score_min NUMBER(10) DEFAULT NULL, 
                score_max NUMBER(10) DEFAULT NULL, 
                score_scaled NUMERIC(10, 7) DEFAULT NULL, 
                completion_status VARCHAR2(255) DEFAULT NULL, 
                success_status VARCHAR2(255) DEFAULT NULL, 
                total_time VARCHAR2(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SCORM_2004_SCO_TRACKING' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_2004_SCO_TRACKING ADD CONSTRAINT CLARO_SCORM_2004_SCO_TRACKING_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_2004_SCO_TRACKING_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_2004_SCO_TRACKING_AI_PK BEFORE INSERT ON CLARO_SCORM_2004_SCO_TRACKING FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_2004_SCO_TRACKING_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_2004_SCO_TRACKING_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_2004_SCO_TRACKING_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_2004_SCO_TRACKING_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61CA76ED395 ON claro_scorm_2004_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61C18A32826 ON claro_scorm_2004_sco_tracking (sco_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_scorm_2004_sco_tracking.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_2004_resource (
                id NUMBER(10) NOT NULL, 
                hash_name VARCHAR2(50) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SCORM_2004_RESOURCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_2004_RESOURCE ADD CONSTRAINT CLARO_SCORM_2004_RESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_2004_RESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_2004_RESOURCE_AI_PK BEFORE INSERT ON CLARO_SCORM_2004_RESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_2004_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_2004_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_2004_RESOURCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_2004_RESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D16AB015B87FAB32 ON claro_scorm_2004_resource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_2004_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD48C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61C18A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_resource 
            ADD CONSTRAINT FK_D16AB015B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking RENAME COLUMN scorm_id TO sco_id
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT FK_465499F3D75F22BE
        ");
        $this->addSql("
            DROP INDEX IDX_465499F3D75F22BE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F318A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking (sco_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            DROP CONSTRAINT FK_E88F1DDD48C689D5
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            DROP CONSTRAINT FK_3A61C18A32826
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            DROP CONSTRAINT FK_E88F1DDD167AFF3D
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_sco
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_sco_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_resource
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking RENAME COLUMN sco_id TO scorm_id
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT FK_465499F318A32826
        ");
        $this->addSql("
            DROP INDEX IDX_465499F318A32826
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking (scorm_id)
        ");
    }
}