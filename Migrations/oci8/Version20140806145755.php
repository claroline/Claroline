<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/06 02:57:56
 */
class Version20140806145755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_model (
                user_id NUMBER(10) NOT NULL, 
                model_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(user_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_57DE02DBA76ED395 ON claro_user_model (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_57DE02DB7975B7E7 ON claro_user_model (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_group_model (
                group_id NUMBER(10) NOT NULL, 
                model_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(group_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C6568DFFE54D947 ON claro_group_model (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C6568DF7975B7E7 ON claro_group_model (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_model (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MODEL' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MODEL ADD CONSTRAINT CLARO_MODEL_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MODEL_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MODEL_AI_PK BEFORE INSERT ON CLARO_MODEL FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MODEL_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MODEL_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_5D96A5CB82D40A1F ON claro_model (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_group_home_tab (
                hometab_id NUMBER(10) NOT NULL, 
                model_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(hometab_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E8BB4D96CCE862F ON claro_group_home_tab (hometab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E8BB4D967975B7E7 ON claro_group_home_tab (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_model (
                id NUMBER(10) NOT NULL, 
                model_id NUMBER(10) NOT NULL, 
                isCopy NUMBER(1) NOT NULL, 
                resourceNode_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_RESOURCE_MODEL' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_RESOURCE_MODEL ADD CONSTRAINT CLARO_RESOURCE_MODEL_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_RESOURCE_MODEL_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_RESOURCE_MODEL_AI_PK BEFORE INSERT ON CLARO_RESOURCE_MODEL FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_RESOURCE_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_RESOURCE_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_RESOURCE_MODEL_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_RESOURCE_MODEL_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_FC03303AB87FAB32 ON claro_resource_model (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FC03303A7975B7E7 ON claro_resource_model (model_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_model 
            ADD CONSTRAINT FK_57DE02DBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_model 
            ADD CONSTRAINT FK_57DE02DB7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            ADD CONSTRAINT FK_C6568DFFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            ADD CONSTRAINT FK_C6568DF7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_model 
            ADD CONSTRAINT FK_5D96A5CB82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            ADD CONSTRAINT FK_E8BB4D96CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            ADD CONSTRAINT FK_E8BB4D967975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            ADD CONSTRAINT FK_FC03303AB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            ADD CONSTRAINT FK_FC03303A7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_model 
            DROP CONSTRAINT FK_57DE02DB7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            DROP CONSTRAINT FK_C6568DF7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            DROP CONSTRAINT FK_E8BB4D967975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            DROP CONSTRAINT FK_FC03303A7975B7E7
        ");
        $this->addSql("
            DROP TABLE claro_user_model
        ");
        $this->addSql("
            DROP TABLE claro_group_model
        ");
        $this->addSql("
            DROP TABLE claro_model
        ");
        $this->addSql("
            DROP TABLE claro_group_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_resource_model
        ");
    }
}