<?php

namespace Icap\BlogBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/20 01:37:19
 */
class Version20130820133717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_tag (
                id NUMBER(10) NOT NULL,
                name VARCHAR2(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'ICAP__BLOG_TAG'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_TAG ADD CONSTRAINT ICAP__BLOG_TAG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_TAG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_TAG_AI_PK BEFORE INSERT ON ICAP__BLOG_TAG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT ICAP__BLOG_TAG_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT ICAP__BLOG_TAG_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'ICAP__BLOG_TAG_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT ICAP__BLOG_TAG_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8BE678285E237E06 ON icap__blog_tag (name)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_post (
                id NUMBER(10) NOT NULL,
                user_id NUMBER(10) DEFAULT NULL,
                blog_id NUMBER(10) DEFAULT NULL,
                title VARCHAR2(255) NOT NULL,
                content CLOB NOT NULL,
                slug VARCHAR2(128) NOT NULL,
                creation_date TIMESTAMP(0) NOT NULL,
                modification_date TIMESTAMP(0) NOT NULL,
                publication_date TIMESTAMP(0) DEFAULT NULL,
                status NUMBER(5) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'ICAP__BLOG_POST'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_POST ADD CONSTRAINT ICAP__BLOG_POST_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_POST_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_POST_AI_PK BEFORE INSERT ON ICAP__BLOG_POST FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT ICAP__BLOG_POST_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT ICAP__BLOG_POST_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'ICAP__BLOG_POST_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT ICAP__BLOG_POST_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1B067922989D9B62 ON icap__blog_post (slug)
        ");
        $this->addSql("
            CREATE INDEX IDX_1B067922A76ED395 ON icap__blog_post (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1B067922DAE07E97 ON icap__blog_post (blog_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_post_tag (
                post_id NUMBER(10) NOT NULL,
                tag_id NUMBER(10) NOT NULL,
                PRIMARY KEY(post_id, tag_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C3C6F4794B89032C ON icap__blog_post_tag (post_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3C6F479BAD26311 ON icap__blog_post_tag (tag_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_comment (
                id NUMBER(10) NOT NULL,
                user_id NUMBER(10) DEFAULT NULL,
                post_id NUMBER(10) DEFAULT NULL,
                message CLOB NOT NULL,
                creation_date TIMESTAMP(0) NOT NULL,
                publication_date TIMESTAMP(0) DEFAULT NULL,
                status NUMBER(5) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'ICAP__BLOG_COMMENT'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_COMMENT ADD CONSTRAINT ICAP__BLOG_COMMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_COMMENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_COMMENT_AI_PK BEFORE INSERT ON ICAP__BLOG_COMMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT ICAP__BLOG_COMMENT_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT ICAP__BLOG_COMMENT_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'ICAP__BLOG_COMMENT_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT ICAP__BLOG_COMMENT_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_95EB616FA76ED395 ON icap__blog_comment (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_95EB616F4B89032C ON icap__blog_comment (post_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog (
                id NUMBER(10) NOT NULL,
                infos CLOB DEFAULT NULL,
                resourceNode_id NUMBER(10) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'ICAP__BLOG'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG ADD CONSTRAINT ICAP__BLOG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_AI_PK BEFORE INSERT ON ICAP__BLOG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT ICAP__BLOG_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT ICAP__BLOG_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'ICAP__BLOG_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT ICAP__BLOG_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_options (
                id NUMBER(10) NOT NULL,
                blog_id NUMBER(10) DEFAULT NULL,
                authorize_comment NUMBER(1) NOT NULL,
                authorize_anonymous_comment NUMBER(1) NOT NULL,
                post_per_page NUMBER(5) NOT NULL,
                auto_publish_post NUMBER(1) NOT NULL,
                auto_publish_comment NUMBER(1) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'ICAP__BLOG_OPTIONS'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_OPTIONS ADD CONSTRAINT ICAP__BLOG_OPTIONS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_OPTIONS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_OPTIONS_AI_PK BEFORE INSERT ON ICAP__BLOG_OPTIONS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT ICAP__BLOG_OPTIONS_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; IF (
                :NEW.ID IS NULL
                OR :NEW.ID = 0
            ) THEN
            SELECT ICAP__BLOG_OPTIONS_ID_SEQ.NEXTVAL INTO :NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'ICAP__BLOG_OPTIONS_ID_SEQ';
            SELECT :NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT ICAP__BLOG_OPTIONS_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post
            ADD CONSTRAINT FK_1B067922A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post
            ADD CONSTRAINT FK_1B067922DAE07E97 FOREIGN KEY (blog_id)
            REFERENCES icap__blog (id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post_tag
            ADD CONSTRAINT FK_C3C6F4794B89032C FOREIGN KEY (post_id)
            REFERENCES icap__blog_post (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post_tag
            ADD CONSTRAINT FK_C3C6F479BAD26311 FOREIGN KEY (tag_id)
            REFERENCES icap__blog_tag (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__blog_comment
            ADD CONSTRAINT FK_95EB616FA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_comment
            ADD CONSTRAINT FK_95EB616F4B89032C FOREIGN KEY (post_id)
            REFERENCES icap__blog_post (id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog
            ADD CONSTRAINT FK_FD75E6C4B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options
            ADD CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id)
            REFERENCES icap__blog (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_post_tag
            DROP CONSTRAINT FK_C3C6F479BAD26311
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post_tag
            DROP CONSTRAINT FK_C3C6F4794B89032C
        ");
        $this->addSql("
            ALTER TABLE icap__blog_comment
            DROP CONSTRAINT FK_95EB616F4B89032C
        ");
        $this->addSql("
            ALTER TABLE icap__blog_post
            DROP CONSTRAINT FK_1B067922DAE07E97
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options
            DROP CONSTRAINT FK_D1AAC984DAE07E97
        ");
        $this->addSql("
            DROP TABLE icap__blog_tag
        ");
        $this->addSql("
            DROP TABLE icap__blog_post
        ");
        $this->addSql("
            DROP TABLE icap__blog_post_tag
        ");
        $this->addSql("
            DROP TABLE icap__blog_comment
        ");
        $this->addSql("
            DROP TABLE icap__blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_options
        ");
    }
}
