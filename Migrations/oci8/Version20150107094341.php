<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/07 09:43:42
 */
class Version20150107094341 extends AbstractMigration
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
            CREATE TABLE claro_personal_workspace_resource_rights_management_access (
                id NUMBER(10) NOT NULL, 
                role_id NUMBER(10) NOT NULL, 
                is_accessible NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS ADD CONSTRAINT CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_AI_PK BEFORE INSERT ON CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_PERSONAL_WORKSPACE_RESOURCE_RIGHTS_MANAGEMENT_ACCESS_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A3AE069AD60322AC ON claro_personal_workspace_resource_rights_management_access (role_id)
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
            ALTER TABLE claro_personal_workspace_resource_rights_management_access 
            ADD CONSTRAINT FK_A3AE069AD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF2DE62210 ON claro_resource_node (previous_id)
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
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD (
                is_upload_destination NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_personnal_workspace_tool_config
        ");
        $this->addSql("
            DROP TABLE claro_personal_workspace_resource_rights_management_access
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_E2EE25E281C06096
        ");
        $this->addSql("
            DROP INDEX IDX_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP (is_upload_destination)
        ");
        $this->addSql("
            DROP INDEX IDX_EA81C80BB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF2DE62210
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_5D9559DCB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_EB8D285282D40A1F
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