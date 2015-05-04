<?php

namespace Icap\SocialmediaBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 05:00:13
 */
class Version20150427170011 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__socialmedia_share (
                id NUMBER(10) NOT NULL, 
                resource_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                network VARCHAR2(255) DEFAULT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__SOCIALMEDIA_SHARE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__SOCIALMEDIA_SHARE ADD CONSTRAINT ICAP__SOCIALMEDIA_SHARE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__SOCIALMEDIA_SHARE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__SOCIALMEDIA_SHARE_AI_PK BEFORE INSERT ON ICAP__SOCIALMEDIA_SHARE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__SOCIALMEDIA_SHARE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__SOCIALMEDIA_SHARE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__SOCIALMEDIA_SHARE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__SOCIALMEDIA_SHARE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_4DB117C589329D25 ON icap__socialmedia_share (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4DB117C5A76ED395 ON icap__socialmedia_share (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_like (
                id NUMBER(10) NOT NULL, 
                resource_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__SOCIALMEDIA_LIKE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__SOCIALMEDIA_LIKE ADD CONSTRAINT ICAP__SOCIALMEDIA_LIKE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__SOCIALMEDIA_LIKE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__SOCIALMEDIA_LIKE_AI_PK BEFORE INSERT ON ICAP__SOCIALMEDIA_LIKE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__SOCIALMEDIA_LIKE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__SOCIALMEDIA_LIKE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__SOCIALMEDIA_LIKE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__SOCIALMEDIA_LIKE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_7C98AD9089329D25 ON icap__socialmedia_like (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7C98AD90A76ED395 ON icap__socialmedia_like (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_comment (
                id NUMBER(10) NOT NULL, 
                resource_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__SOCIALMEDIA_COMMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__SOCIALMEDIA_COMMENT ADD CONSTRAINT ICAP__SOCIALMEDIA_COMMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__SOCIALMEDIA_COMMENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__SOCIALMEDIA_COMMENT_AI_PK BEFORE INSERT ON ICAP__SOCIALMEDIA_COMMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__SOCIALMEDIA_COMMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__SOCIALMEDIA_COMMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__SOCIALMEDIA_COMMENT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__SOCIALMEDIA_COMMENT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_6FC00C3089329D25 ON icap__socialmedia_comment (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6FC00C30A76ED395 ON icap__socialmedia_comment (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_wall_item (
                id NUMBER(10) NOT NULL, 
                like_id NUMBER(10) DEFAULT NULL, 
                share_id NUMBER(10) DEFAULT NULL, 
                comment_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__SOCIALMEDIA_WALL_ITEM' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__SOCIALMEDIA_WALL_ITEM ADD CONSTRAINT ICAP__SOCIALMEDIA_WALL_ITEM_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__SOCIALMEDIA_WALL_ITEM_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__SOCIALMEDIA_WALL_ITEM_AI_PK BEFORE INSERT ON ICAP__SOCIALMEDIA_WALL_ITEM FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__SOCIALMEDIA_WALL_ITEM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__SOCIALMEDIA_WALL_ITEM_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__SOCIALMEDIA_WALL_ITEM_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__SOCIALMEDIA_WALL_ITEM_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420859BFA32 ON icap__socialmedia_wall_item (like_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC4202AE63FDB ON icap__socialmedia_wall_item (share_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420F8697D13 ON icap__socialmedia_wall_item (comment_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420A76ED395 ON icap__socialmedia_wall_item (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_note (
                id NUMBER(10) NOT NULL, 
                resource_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__SOCIALMEDIA_NOTE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__SOCIALMEDIA_NOTE ADD CONSTRAINT ICAP__SOCIALMEDIA_NOTE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__SOCIALMEDIA_NOTE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__SOCIALMEDIA_NOTE_AI_PK BEFORE INSERT ON ICAP__SOCIALMEDIA_NOTE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__SOCIALMEDIA_NOTE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__SOCIALMEDIA_NOTE_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__SOCIALMEDIA_NOTE_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__SOCIALMEDIA_NOTE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_1F46173789329D25 ON icap__socialmedia_note (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1F461737A76ED395 ON icap__socialmedia_note (user_id)
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_share 
            ADD CONSTRAINT FK_4DB117C589329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_share 
            ADD CONSTRAINT FK_4DB117C5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_like 
            ADD CONSTRAINT FK_7C98AD9089329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_like 
            ADD CONSTRAINT FK_7C98AD90A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_comment 
            ADD CONSTRAINT FK_6FC00C3089329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_comment 
            ADD CONSTRAINT FK_6FC00C30A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            ADD CONSTRAINT FK_436BC420859BFA32 FOREIGN KEY (like_id) 
            REFERENCES icap__socialmedia_like (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            ADD CONSTRAINT FK_436BC4202AE63FDB FOREIGN KEY (share_id) 
            REFERENCES icap__socialmedia_share (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            ADD CONSTRAINT FK_436BC420F8697D13 FOREIGN KEY (comment_id) 
            REFERENCES icap__socialmedia_comment (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            ADD CONSTRAINT FK_436BC420A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_note 
            ADD CONSTRAINT FK_1F46173789329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_note 
            ADD CONSTRAINT FK_1F461737A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            DROP CONSTRAINT FK_436BC4202AE63FDB
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            DROP CONSTRAINT FK_436BC420859BFA32
        ");
        $this->addSql("
            ALTER TABLE icap__socialmedia_wall_item 
            DROP CONSTRAINT FK_436BC420F8697D13
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_share
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_like
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_comment
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_wall_item
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_note
        ");
    }
}