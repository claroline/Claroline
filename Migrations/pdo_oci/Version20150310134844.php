<?php

namespace Innova\CollecticielBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/10 01:48:47
 */
class Version20150310134844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_comment_read (
                id NUMBER(10) NOT NULL, 
                comment_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_COMMENT_READ' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_COMMENT_READ ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_COMMENT_READ FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_READ_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_83EB06B9F8697D13 ON innova_collecticielbundle_comment_read (comment_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_83EB06B9A76ED395 ON innova_collecticielbundle_comment_read (user_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_comment (
                id NUMBER(10) NOT NULL, 
                document_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                commentText CLOB DEFAULT NULL NULL, 
                comment_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_COLLECTICIELBUNDLE_COMMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_COLLECTICIELBUNDLE_COMMENT ADD CONSTRAINT INNOVA_COLLECTICIELBUNDLE_COMMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_COLLECTICIELBUNDLE_COMMENT_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_COLLECTICIELBUNDLE_COMMENT_AI_PK BEFORE INSERT ON INNOVA_COLLECTICIELBUNDLE_COMMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_COLLECTICIELBUNDLE_COMMENT_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_COLLECTICIELBUNDLE_COMMENT_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095C33F7837 ON innova_collecticielbundle_comment (document_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095A76ED395 ON innova_collecticielbundle_comment (user_id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment_read 
            ADD CONSTRAINT FK_83EB06B9F8697D13 FOREIGN KEY (comment_id) 
            REFERENCES innova_collecticielbundle_comment (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment_read 
            ADD CONSTRAINT FK_83EB06B9A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment 
            ADD CONSTRAINT FK_A9CB9095C33F7837 FOREIGN KEY (document_id) 
            REFERENCES innova_collecticielbundle_document (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment 
            ADD CONSTRAINT FK_A9CB9095A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            DROP CONSTRAINT FK_1C357F0C4D224760
        ");
        $this->addSql("
            DROP INDEX IDX_1C357F0C4D224760
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            DROP (drop_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment_read 
            DROP CONSTRAINT FK_83EB06B9F8697D13
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_comment_read
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_comment
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD (
                drop_id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ");
    }
}