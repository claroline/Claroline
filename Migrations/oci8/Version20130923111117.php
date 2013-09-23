<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:11:17
 */
class Version20130923111117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_stepType (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_STEPTYPE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEPTYPE ADD CONSTRAINT INNOVA_STEPTYPE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEPTYPE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEPTYPE_AI_PK BEFORE INSERT ON INNOVA_STEPTYPE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_STEPTYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_STEPTYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_STEPTYPE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_STEPTYPE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id NUMBER(10) NOT NULL, 
                step_id NUMBER(10) DEFAULT NULL, 
                resourceOrder NUMBER(10) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_STEP2RESOURCENODE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEP2RESOURCENODE ADD CONSTRAINT INNOVA_STEP2RESOURCENODE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEP2RESOURCENODE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEP2RESOURCENODE_AI_PK BEFORE INSERT ON INNOVA_STEP2RESOURCENODE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_STEP2RESOURCENODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_STEP2RESOURCENODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_STEP2RESOURCENODE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_STEP2RESOURCENODE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                path_id NUMBER(10) NOT NULL, 
                status NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_USER2PATH' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_USER2PATH ADD CONSTRAINT INNOVA_USER2PATH_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_USER2PATH_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_USER2PATH_AI_PK BEFORE INSERT ON INNOVA_USER2PATH FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_USER2PATH_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_USER2PATH_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_USER2PATH_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_USER2PATH_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                description CLOB NOT NULL, 
                step CLOB NOT NULL, 
                \"user\" VARCHAR2(255) NOT NULL, 
                edit_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_PATHTEMPLATE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_PATHTEMPLATE ADD CONSTRAINT INNOVA_PATHTEMPLATE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_PATHTEMPLATE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_PATHTEMPLATE_AI_PK BEFORE INSERT ON INNOVA_PATHTEMPLATE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_PATHTEMPLATE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_PATHTEMPLATE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_PATHTEMPLATE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_PATHTEMPLATE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_STEPWHERE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEPWHERE ADD CONSTRAINT INNOVA_STEPWHERE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEPWHERE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEPWHERE_AI_PK BEFORE INSERT ON INNOVA_STEPWHERE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_STEPWHERE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_STEPWHERE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_STEPWHERE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_STEPWHERE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_STEPWHO' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEPWHO ADD CONSTRAINT INNOVA_STEPWHO_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEPWHO_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEPWHO_AI_PK BEFORE INSERT ON INNOVA_STEPWHO FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_STEPWHO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_STEPWHO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_STEPWHO_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_STEPWHO_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id NUMBER(10) NOT NULL, 
                description CLOB NOT NULL, 
                type VARCHAR2(255) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_NONDIGITALRESOURCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_NONDIGITALRESOURCE ADD CONSTRAINT INNOVA_NONDIGITALRESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_NONDIGITALRESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_NONDIGITALRESOURCE_AI_PK BEFORE INSERT ON INNOVA_NONDIGITALRESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_NONDIGITALRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_NONDIGITALRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_NONDIGITALRESOURCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_NONDIGITALRESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_305E9E56B87FAB32 ON innova_nonDigitalResource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id NUMBER(10) NOT NULL, 
                path_id NUMBER(10) DEFAULT NULL, 
                stepOrder NUMBER(10) NOT NULL, 
                parent VARCHAR2(255) DEFAULT NULL, 
                expanded NUMBER(1) NOT NULL, 
                instructions CLOB NOT NULL, 
                withTutor NUMBER(1) NOT NULL, 
                withComputer NUMBER(1) NOT NULL, 
                duration TIMESTAMP(0) NOT NULL, 
                stepType_id NUMBER(10) DEFAULT NULL, 
                stepWho_id NUMBER(10) DEFAULT NULL, 
                stepWhere_id NUMBER(10) DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_STEP' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEP ADD CONSTRAINT INNOVA_STEP_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEP_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEP_AI_PK BEFORE INSERT ON INNOVA_STEP FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_STEP_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_STEP_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_STEP_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_STEP_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_86F48567B87FAB32 ON innova_step (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE innova_user2path 
            ADD CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_user2path 
            ADD CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD CONSTRAINT FK_305E9E56B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
            REFERENCES innova_stepWho (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
            REFERENCES innova_stepWhere (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F485678FE76F3
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP CONSTRAINT FK_21EA11F73B21E9C
        ");
        $this->addSql("
            DROP TABLE innova_stepType
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_stepWho
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
    }
}