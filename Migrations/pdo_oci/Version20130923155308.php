<?php

namespace Icap\LessonBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 03:53:10
 */
class Version20130923155308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__lesson_chapter (
                id NUMBER(10) NOT NULL, 
                lesson_id NUMBER(10) DEFAULT NULL, 
                parent_id NUMBER(10) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                lft NUMBER(10) NOT NULL, 
                lvl NUMBER(10) NOT NULL, 
                rgt NUMBER(10) NOT NULL, 
                root NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__LESSON_CHAPTER' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__LESSON_CHAPTER ADD CONSTRAINT ICAP__LESSON_CHAPTER_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__LESSON_CHAPTER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__LESSON_CHAPTER_AI_PK BEFORE INSERT ON ICAP__LESSON_CHAPTER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__LESSON_CHAPTER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__LESSON_CHAPTER_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__LESSON_CHAPTER_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__LESSON_CHAPTER_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_3D7E3C8CCDF80196 ON icap__lesson_chapter (lesson_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3D7E3C8C727ACA70 ON icap__lesson_chapter (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__lesson (
                id NUMBER(10) NOT NULL, 
                root_id NUMBER(10) DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__LESSON' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__LESSON ADD CONSTRAINT ICAP__LESSON_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__LESSON_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__LESSON_AI_PK BEFORE INSERT ON ICAP__LESSON FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__LESSON_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__LESSON_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__LESSON_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__LESSON_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B3613079066886 ON icap__lesson (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B36130B87FAB32 ON icap__lesson (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8CCDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES icap__lesson (id)
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B3613079066886 FOREIGN KEY (root_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B36130B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            DROP CONSTRAINT FK_3D7E3C8C727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            DROP CONSTRAINT FK_D9B3613079066886
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            DROP CONSTRAINT FK_3D7E3C8CCDF80196
        ");
        $this->addSql("
            DROP TABLE icap__lesson_chapter
        ");
        $this->addSql("
            DROP TABLE icap__lesson
        ");
    }
}