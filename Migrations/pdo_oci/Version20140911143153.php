<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 02:31:54
 */
class Version20140911143153 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_model_user (
                user_id NUMBER(10) NOT NULL, 
                workspacemodel_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(user_id, workspacemodel_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388FA76ED395 ON claro_workspace_model_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388FD500BD91 ON claro_workspace_model_user (workspacemodel_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_group (
                group_id NUMBER(10) NOT NULL, 
                workspacemodel_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(group_id, workspacemodel_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AEFE54D947 ON claro_workspace_model_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AED500BD91 ON claro_workspace_model_group (workspacemodel_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
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
            WHERE TABLE_NAME = 'CLARO_WORKSPACE_MODEL' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_WORKSPACE_MODEL ADD CONSTRAINT CLARO_WORKSPACE_MODEL_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_WORKSPACE_MODEL_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_WORKSPACE_MODEL_AI_PK BEFORE INSERT ON CLARO_WORKSPACE_MODEL FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_WORKSPACE_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_WORKSPACE_MODEL_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_WORKSPACE_MODEL_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_WORKSPACE_MODEL_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_536FFC4C82D40A1F ON claro_workspace_model (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_home_tab (
                workspacemodel_id NUMBER(10) NOT NULL, 
                hometab_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(workspacemodel_id, hometab_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1BD500BD91 ON claro_workspace_model_home_tab (workspacemodel_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1BCCE862F ON claro_workspace_model_home_tab (hometab_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_resource (
                id NUMBER(10) NOT NULL, 
                resource_node_id NUMBER(10) NOT NULL, 
                model_id NUMBER(10) NOT NULL, 
                isCopy NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_WORKSPACE_MODEL_RESOURCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_WORKSPACE_MODEL_RESOURCE ADD CONSTRAINT CLARO_WORKSPACE_MODEL_RESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_WORKSPACE_MODEL_RESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_WORKSPACE_MODEL_RESOURCE_AI_PK BEFORE INSERT ON CLARO_WORKSPACE_MODEL_RESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_WORKSPACE_MODEL_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_WORKSPACE_MODEL_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_WORKSPACE_MODEL_RESOURCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_WORKSPACE_MODEL_RESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D706351BAD783F ON claro_workspace_model_resource (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D706357975B7E7 ON claro_workspace_model_resource (model_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388FD500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AEFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AED500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model 
            ADD CONSTRAINT FK_536FFC4C82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1BD500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1BCCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706351BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706357975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            DROP CONSTRAINT FK_5318388FD500BD91
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            DROP CONSTRAINT FK_1F19A8AED500BD91
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            DROP CONSTRAINT FK_A8E0CB1BD500BD91
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            DROP CONSTRAINT FK_F5D706357975B7E7
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_user
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_group
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_resource
        ");
    }
}