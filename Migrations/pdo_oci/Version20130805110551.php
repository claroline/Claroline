<?php

namespace Claroline\ScormBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/05 11:05:53
 */
class Version20130805110551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_info (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                scorm_id NUMBER(10) DEFAULT NULL, 
                score_raw NUMBER(10) DEFAULT NULL, 
                score_min NUMBER(10) DEFAULT NULL, 
                score_max NUMBER(10) DEFAULT NULL, 
                lesson_status VARCHAR2(255) DEFAULT NULL, 
                session_time NUMBER(10) DEFAULT NULL, 
                total_time NUMBER(10) DEFAULT NULL, 
                entry VARCHAR2(255) DEFAULT NULL, 
                suspend_data VARCHAR2(255) DEFAULT NULL, 
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
            WHERE TABLE_NAME = 'CLARO_SCORM_INFO' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SCORM_INFO ADD CONSTRAINT CLARO_SCORM_INFO_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SCORM_INFO_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SCORM_INFO_AI_PK BEFORE INSERT ON CLARO_SCORM_INFO FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SCORM_INFO_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_SCORM_INFO_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SCORM_INFO_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SCORM_INFO_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916A76ED395 ON claro_scorm_info (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916D75F22BE ON claro_scorm_info (scorm_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm (
                id NUMBER(10) NOT NULL, 
                hash_name VARCHAR2(36) NOT NULL, 
                mastery_score NUMBER(10) DEFAULT NULL, 
                launch_data VARCHAR2(255) DEFAULT NULL, 
                entry_url VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm (id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm 
            ADD CONSTRAINT FK_B6416871BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            DROP CONSTRAINT FK_6F4BB916D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_info
        ");
        $this->addSql("
            DROP TABLE claro_scorm
        ");
    }
}