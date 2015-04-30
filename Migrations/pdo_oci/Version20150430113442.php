<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/30 11:34:43
 */
class Version20150430113442 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id NUMBER(10) NOT NULL, 
                objective_id NUMBER(10) NOT NULL, 
                competency_id NUMBER(10) NOT NULL, 
                level_id NUMBER(10) NOT NULL, 
                framework_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_OBJECTIVE_COMPETENCY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_OBJECTIVE_COMPETENCY ADD CONSTRAINT HEVINCI_OBJECTIVE_COMPETENCY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE HEVINCI_OBJECTIVE_COMPETENCY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER HEVINCI_OBJECTIVE_COMPETENCY_AI_PK BEFORE INSERT ON HEVINCI_OBJECTIVE_COMPETENCY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_OBJECTIVE_COMPETENCY_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854473484933 ON hevinci_objective_competency (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF8544FB9F58C ON hevinci_objective_competency (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF85445FB14BA7 ON hevinci_objective_competency (level_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854437AECF72 ON hevinci_objective_competency (framework_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability_progress (
                id NUMBER(10) NOT NULL, 
                ability_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                passed_activity_ids CLOB DEFAULT NULL, 
                passed_activity_count NUMBER(10) NOT NULL, 
                status VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_ABILITY_PROGRESS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_ABILITY_PROGRESS ADD CONSTRAINT HEVINCI_ABILITY_PROGRESS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE HEVINCI_ABILITY_PROGRESS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER HEVINCI_ABILITY_PROGRESS_AI_PK BEFORE INSERT ON HEVINCI_ABILITY_PROGRESS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_ABILITY_PROGRESS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_ABILITY_PROGRESS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_ABILITY_PROGRESS_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_ABILITY_PROGRESS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62E8016D8B2 ON hevinci_ability_progress (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62EA76ED395 ON hevinci_ability_progress (user_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN hevinci_ability_progress.passed_activity_ids IS '(DC2Type:simple_array)'
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_progress (
                id NUMBER(10) NOT NULL, 
                competency_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                level_id NUMBER(10) DEFAULT NULL, 
                type VARCHAR2(255) NOT NULL, 
                \"date\" TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_COMPETENCY_PROGRESS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_COMPETENCY_PROGRESS ADD CONSTRAINT HEVINCI_COMPETENCY_PROGRESS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE HEVINCI_COMPETENCY_PROGRESS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER HEVINCI_COMPETENCY_PROGRESS_AI_PK BEFORE INSERT ON HEVINCI_COMPETENCY_PROGRESS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_COMPETENCY_PROGRESS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_COMPETENCY_PROGRESS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_COMPETENCY_PROGRESS_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_COMPETENCY_PROGRESS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3FB9F58C ON hevinci_competency_progress (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3A76ED395 ON hevinci_competency_progress (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A35FB14BA7 ON hevinci_competency_progress (level_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_learning_objective (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_LEARNING_OBJECTIVE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_LEARNING_OBJECTIVE ADD CONSTRAINT HEVINCI_LEARNING_OBJECTIVE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE HEVINCI_LEARNING_OBJECTIVE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER HEVINCI_LEARNING_OBJECTIVE_AI_PK BEFORE INSERT ON HEVINCI_LEARNING_OBJECTIVE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_LEARNING_OBJECTIVE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_LEARNING_OBJECTIVE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_LEARNING_OBJECTIVE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_LEARNING_OBJECTIVE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_10D9D6545E237E06 ON hevinci_learning_objective (name)
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_user (
                objective_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(objective_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C1573484933 ON hevinci_objective_user (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C15A76ED395 ON hevinci_objective_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_group (
                objective_id NUMBER(10) NOT NULL, 
                group_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(objective_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E073484933 ON hevinci_objective_group (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E0FE54D947 ON hevinci_objective_group (group_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854437AECF72 FOREIGN KEY (framework_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A35FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C1573484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C15A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E073484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E0FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            DROP CONSTRAINT FK_EDBF854473484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            DROP CONSTRAINT FK_6D032C1573484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            DROP CONSTRAINT FK_FFDC9E073484933
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_ability_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_learning_objective
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_user
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_group
        ");
    }
}