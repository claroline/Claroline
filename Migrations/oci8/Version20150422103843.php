<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/22 10:38:44
 */
class Version20150422103843 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_ordered_tool_translation (
                id NUMBER(10) NOT NULL, 
                locale VARCHAR2(8) NOT NULL, 
                object_class VARCHAR2(255) NOT NULL, 
                field VARCHAR2(32) NOT NULL, 
                foreign_key VARCHAR2(64) NOT NULL, 
                content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ORDERED_TOOL_TRANSLATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ORDERED_TOOL_TRANSLATION ADD CONSTRAINT CLARO_ORDERED_TOOL_TRANSLATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ORDERED_TOOL_TRANSLATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ORDERED_TOOL_TRANSLATION_AI_PK BEFORE INSERT ON CLARO_ORDERED_TOOL_TRANSLATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ORDERED_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_ORDERED_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ORDERED_TOOL_TRANSLATION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ORDERED_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX tool_ordered_translation_idx ON claro_ordered_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            CREATE TABLE claro_tool_translation (
                id NUMBER(10) NOT NULL, 
                locale VARCHAR2(8) NOT NULL, 
                object_class VARCHAR2(255) NOT NULL, 
                field VARCHAR2(32) NOT NULL, 
                foreign_key VARCHAR2(64) NOT NULL, 
                content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_TOOL_TRANSLATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_TOOL_TRANSLATION ADD CONSTRAINT CLARO_TOOL_TRANSLATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_TOOL_TRANSLATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_TOOL_TRANSLATION_AI_PK BEFORE INSERT ON CLARO_TOOL_TRANSLATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_TOOL_TRANSLATION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_TOOL_TRANSLATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX tool_translation_idx ON claro_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD (
                displayedName VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP (name)
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_tools RENAME COLUMN display_name TO displayedName
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_ordered_tool_translation
        ");
        $this->addSql("
            DROP TABLE claro_tool_translation
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD (
                name VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP (displayedName)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ");
        $this->addSql("
            ALTER TABLE claro_tools RENAME COLUMN displayedname TO display_name
        ");
    }
}