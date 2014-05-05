<?php

namespace Claroline\ScormBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/05 02:12:43
 */
class Version20140505141242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_12_tracking (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                scorm_id NUMBER(10) NOT NULL, 
                score_raw NUMBER(10) DEFAULT NULL, 
                score_min NUMBER(10) DEFAULT NULL, 
                score_max NUMBER(10) DEFAULT NULL, 
                lesson_status VARCHAR2(255) DEFAULT NULL, 
                session_time NUMBER(10) DEFAULT NULL, 
                total_time NUMBER(10) DEFAULT NULL, 
                entry VARCHAR2(255) DEFAULT NULL, 
                suspend_data CLOB DEFAULT NULL, 
                credit VARCHAR2(255) DEFAULT NULL, 
                exit_mode VARCHAR2(255) DEFAULT NULL, 
                lesson_location VARCHAR2(255) DEFAULT NULL, 
                lesson_mode VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SCORM_12_TRACKING' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_12_TRACKING ADD CONSTRAINT CLARO_SCORM_12_TRACKING_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_12_TRACKING_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_12_TRACKING_AI_PK BEFORE INSERT ON CLARO_SCORM_12_TRACKING FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_12_TRACKING_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_12_TRACKING_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_12_TRACKING_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_12_TRACKING_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_CF939976A76ED395 ON claro_scorm_12_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CF939976D75F22BE ON claro_scorm_12_tracking (scorm_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12 (
                id NUMBER(10) NOT NULL, 
                hash_name VARCHAR2(50) NOT NULL, 
                mastery_score NUMBER(10) DEFAULT NULL, 
                launch_data CLOB DEFAULT NULL, 
                entry_url VARCHAR2(255) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SCORM_12' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_12 ADD CONSTRAINT CLARO_SCORM_12_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_12_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_12_AI_PK BEFORE INSERT ON CLARO_SCORM_12 FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_12_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_12_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_12_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_12_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6FE774D5B87FAB32 ON claro_scorm_12 (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_tracking 
            ADD CONSTRAINT FK_CF939976A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_tracking 
            ADD CONSTRAINT FK_CF939976D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12 (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12 
            ADD CONSTRAINT FK_6FE774D5B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_12_tracking 
            DROP CONSTRAINT FK_CF939976D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12
        ");
    }
}