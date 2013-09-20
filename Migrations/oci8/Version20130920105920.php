<?php

namespace Icap\WikiBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 10:59:21
 */
class Version20130920105920 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wikibundle_section (
                id NUMBER(10) NOT NULL, 
                wiki_id NUMBER(10) NOT NULL, 
                parent_id NUMBER(10) DEFAULT NULL, 
                name VARCHAR2(255) NOT NULL, 
                visible NUMBER(1) DEFAULT '0' NOT NULL, 
                text CLOB DEFAULT NULL, 
                created TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WIKIBUNDLE_SECTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WIKIBUNDLE_SECTION ADD CONSTRAINT ICAP__WIKIBUNDLE_SECTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WIKIBUNDLE_SECTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WIKIBUNDLE_SECTION_AI_PK BEFORE INSERT ON ICAP__WIKIBUNDLE_SECTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WIKIBUNDLE_SECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WIKIBUNDLE_SECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WIKIBUNDLE_SECTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WIKIBUNDLE_SECTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F79A2D04AA948DBE ON icap__wikibundle_section (wiki_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F79A2D04727ACA70 ON icap__wikibundle_section (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__wikibundle_wiki (
                id NUMBER(10) NOT NULL, 
                text CLOB DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WIKIBUNDLE_WIKI' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WIKIBUNDLE_WIKI ADD CONSTRAINT ICAP__WIKIBUNDLE_WIKI_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WIKIBUNDLE_WIKI_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WIKIBUNDLE_WIKI_AI_PK BEFORE INSERT ON ICAP__WIKIBUNDLE_WIKI FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WIKIBUNDLE_WIKI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WIKIBUNDLE_WIKI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WIKIBUNDLE_WIKI_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WIKIBUNDLE_WIKI_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_31A29422B87FAB32 ON icap__wikibundle_wiki (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            ADD CONSTRAINT FK_F79A2D04AA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wikibundle_wiki (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            ADD CONSTRAINT FK_F79A2D04727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wikibundle_section (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_wiki 
            ADD CONSTRAINT FK_31A29422B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            DROP CONSTRAINT FK_F79A2D04727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            DROP CONSTRAINT FK_F79A2D04AA948DBE
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_section
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_wiki
        ");
    }
}