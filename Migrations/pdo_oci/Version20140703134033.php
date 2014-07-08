<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/03 01:40:34
 */
class Version20140703134033 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_event_event_category (
                event_id NUMBER(10) NOT NULL, 
                eventcategory_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(event_id, eventcategory_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C71F7E88B ON claro_event_event_category (event_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C29E3B4B5 ON claro_event_event_category (eventcategory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event_category (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_EVENT_CATEGORY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_EVENT_CATEGORY ADD CONSTRAINT CLARO_EVENT_CATEGORY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_EVENT_CATEGORY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_EVENT_CATEGORY_AI_PK BEFORE INSERT ON CLARO_EVENT_CATEGORY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_EVENT_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_EVENT_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_EVENT_CATEGORY_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_EVENT_CATEGORY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_408DC8C05E237E06 ON claro_event_category (name)
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C29E3B4B5 FOREIGN KEY (eventcategory_id) 
            REFERENCES claro_event_category (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            DROP CONSTRAINT FK_858F0D4C29E3B4B5
        ");
        $this->addSql("
            DROP TABLE claro_event_event_category
        ");
        $this->addSql("
            DROP TABLE claro_event_category
        ");
    }
}