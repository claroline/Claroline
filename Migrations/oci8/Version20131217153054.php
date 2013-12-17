<?php

namespace Claroline\ForumBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/17 03:30:55
 */
class Version20131217153054 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum_category (
                id NUMBER(10) NOT NULL, 
                forum_id NUMBER(10) DEFAULT NULL, 
                created TIMESTAMP(0) NOT NULL, 
                modificationDate TIMESTAMP(0) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_FORUM_CATEGORY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_FORUM_CATEGORY ADD CONSTRAINT CLARO_FORUM_CATEGORY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_FORUM_CATEGORY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_FORUM_CATEGORY_AI_PK BEFORE INSERT ON CLARO_FORUM_CATEGORY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_FORUM_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_FORUM_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_FORUM_CATEGORY_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_FORUM_CATEGORY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_2192ACF729CCBAD0 ON claro_forum_category (forum_id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_category 
            ADD CONSTRAINT FK_2192ACF729CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD (
                isSticked NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject RENAME COLUMN forum_id TO category_id
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B29CCBAD0
        ");
        $this->addSql("
            DROP INDEX IDX_273AA20B29CCBAD0
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B12469DE2 FOREIGN KEY (category_id) 
            REFERENCES claro_forum_category (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B12469DE2 ON claro_forum_subject (category_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B12469DE2
        ");
        $this->addSql("
            DROP TABLE claro_forum_category
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject RENAME COLUMN category_id TO forum_id
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP (isSticked)
        ");
        $this->addSql("
            DROP INDEX IDX_273AA20B12469DE2
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
    }
}