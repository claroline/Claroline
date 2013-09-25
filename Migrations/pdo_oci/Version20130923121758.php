<?php

namespace Innova\PathBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 12:17:59
 */
class Version20130923121758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
    }
}
