<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/19 02:46:42
 */
class Version20141219144641 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_personnal_workspace_tool_config (
                id NUMBER(10) NOT NULL, 
                role_id NUMBER(10) NOT NULL, 
                tool_id NUMBER(10) NOT NULL, 
                mask NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG ADD CONSTRAINT CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_AI_PK BEFORE INSERT ON CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_PERSONNAL_WORKSPACE_TOOL_CONFIG_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_7A4A6A64D60322AC ON claro_personnal_workspace_tool_config (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7A4A6A648F7B22CC ON claro_personnal_workspace_tool_config (tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX pws_unique_tool_config ON claro_personnal_workspace_tool_config (tool_id, role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A64D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A648F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD (
                maxStorageSize VARCHAR2(255) NOT NULL, 
                maxUploadResources NUMBER(10) NOT NULL, 
                is_personal NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD (
                is_upload_destination NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_personnal_workspace_tool_config
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP (is_upload_destination)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP (
                maxStorageSize, maxUploadResources, 
                is_personal
            )
        ");
    }
}