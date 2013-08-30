<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/19 02:56:25
 */
class Version20130819145625 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id NUMBER(10) NOT NULL,
                license_id NUMBER(10) DEFAULT NULL,
                resource_type_id NUMBER(10) NOT NULL,
                creator_id NUMBER(10) NOT NULL,
                icon_id NUMBER(10) DEFAULT NULL,
                parent_id NUMBER(10) DEFAULT NULL,
                workspace_id NUMBER(10) NOT NULL,
                next_id NUMBER(10) DEFAULT NULL,
                previous_id NUMBER(10) DEFAULT NULL,
                creation_date TIMESTAMP(0) NOT NULL,
                modification_date TIMESTAMP(0) NOT NULL,
                name VARCHAR2(255) NOT NULL,
                lvl NUMBER(10) DEFAULT NULL,
                path VARCHAR2(3000) DEFAULT NULL,
                mime_type VARCHAR2(255) DEFAULT NULL,
                class VARCHAR2(256) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'CLARO_RESOURCE_NODE'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_RESOURCE_NODE ADD CONSTRAINT CLARO_RESOURCE_NODE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_RESOURCE_NODE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_RESOURCE_NODE_AI_PK BEFORE INSERT ON CLARO_RESOURCE_NODE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT CLARO_RESOURCE_NODE_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT CLARO_RESOURCE_NODE_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'CLARO_RESOURCE_NODE_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT CLARO_RESOURCE_NODE_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id)
            REFERENCES claro_license (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id)
            REFERENCES claro_resource_icon (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights RENAME COLUMN resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP CONSTRAINT FK_3848F48389329D25
        ");
        $this->addSql("
            DROP INDEX IDX_3848F48389329D25
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            DROP (parent_id, class)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            DROP CONSTRAINT FK_AEC62693727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            DROP CONSTRAINT FK_E4A67CACBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity RENAME COLUMN resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP CONSTRAINT FK_DCF37C7E89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E89329D25
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            ADD CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_file
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_file MODIFY (
                id NUMBER(10) NOT NULL,
                hash_name VARCHAR2(50) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_file
            DROP CONSTRAINT FK_EA81C80BBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_file
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_link
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_link MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_link
            DROP CONSTRAINT FK_50B267EABF396750
        ");
        $this->addSql("
            ALTER TABLE claro_link
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            DROP CONSTRAINT FK_12EEC186BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut RENAME COLUMN resource_id TO target_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB8BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB889329D25
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB889329D25
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_text
            ADD (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_text MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_text
            DROP CONSTRAINT FK_5D9559DCBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_text
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision MODIFY (content CLOB NOT NULL)
        ");
        $this->addSql("
            ALTER TABLE claro_log RENAME COLUMN resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP CONSTRAINT FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP CONSTRAINT FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file
            DROP CONSTRAINT FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link
            DROP CONSTRAINT FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            DROP CONSTRAINT FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text
            DROP CONSTRAINT FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_activity MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            ADD CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_directory MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            ADD CONSTRAINT FK_12EEC186BF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_file MODIFY (
                id NUMBER(10) NOT NULL,
                hash_name VARCHAR2(36) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_file
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file
            ADD CONSTRAINT FK_EA81C80BBF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_link MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_link
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link
            ADD CONSTRAINT FK_50B267EABF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log RENAME COLUMN resourcenode_id TO resource_id
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91F89329D25 FOREIGN KEY (resource_id)
            REFERENCES claro_resource (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F89329D25 ON claro_log (resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity RENAME COLUMN resourcenode_id TO resource_id
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7EB87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            ADD CONSTRAINT FK_DCF37C7E89329D25 FOREIGN KEY (resource_id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights RENAME COLUMN resourcenode_id TO resource_id
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            ADD CONSTRAINT FK_3848F48389329D25 FOREIGN KEY (resource_id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F48389329D25 ON claro_resource_rights (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resource_id, role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut RENAME COLUMN target_id TO resource_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8158E0B66
        ");
        $this->addSql("
            DROP INDEX UNIQ_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB8BF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB889329D25 FOREIGN KEY (resource_id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            ADD (
                parent_id NUMBER(10) DEFAULT NULL,
                class VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            ADD CONSTRAINT FK_AEC62693727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_resource_type (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693727ACA70 ON claro_resource_type (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_text MODIFY (
                id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_text
            DROP (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text
            ADD CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id)
            REFERENCES claro_resource (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision MODIFY (
                content VARCHAR2(255) NOT NULL
            )
        ");
    }
}
