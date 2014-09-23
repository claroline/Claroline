<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/23 01:58:30
 */
class Version20140923135827 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
        $this->addSql("
            ALTER TABLE claro_message MODIFY (
                receiver_string VARCHAR2(2047) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_model_resource
        ");
        $this->addSql("
            ALTER TABLE claro_message MODIFY (
                receiver_string VARCHAR2(1023) DEFAULT NULL
            )
        ");
    }
}