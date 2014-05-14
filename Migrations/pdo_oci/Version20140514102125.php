<?php

namespace Icap\PortfolioBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/14 10:21:26
 */
class Version20140514102125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_users (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                portfolio_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_USERS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_USERS ADD CONSTRAINT ICAP__PORTFOLIO_USERS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_USERS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_USERS_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_USERS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_USERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_USERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_USERS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_USERS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_3980F8F8A76ED395 ON icap__portfolio_users (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3980F8F8B96B5643 ON icap__portfolio_users (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_users_unique_idx ON icap__portfolio_users (portfolio_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                title VARCHAR2(128) NOT NULL, 
                slug VARCHAR2(128) NOT NULL, 
                visibility NUMBER(10) NOT NULL, 
                createdAt TIMESTAMP(0) NOT NULL, 
                updatedAt TIMESTAMP(0) NOT NULL, 
                deletedAt TIMESTAMP(0) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO ADD CONSTRAINT ICAP__PORTFOLIO_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio (slug)
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                is_unique NUMBER(1) NOT NULL, 
                is_deletable NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_WIDGET_TYPE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_WIDGET_TYPE ADD CONSTRAINT ICAP__PORTFOLIO_WIDGET_TYPE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_WIDGET_TYPE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_WIDGET_TYPE_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_WIDGET_TYPE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_WIDGET_TYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_WIDGET_TYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_WIDGET_TYPE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_WIDGET_TYPE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3E00FC8F5E237E06 ON icap__portfolio_widget_type (name)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_node (
                id NUMBER(10) NOT NULL, 
                widget_type_id NUMBER(10) NOT NULL, 
                portfolio_id NUMBER(10) NOT NULL, 
                createdAt TIMESTAMP(0) NOT NULL, 
                updatedAt TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_WIDGET_NODE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_WIDGET_NODE ADD CONSTRAINT ICAP__PORTFOLIO_WIDGET_NODE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_WIDGET_NODE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_WIDGET_NODE_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_WIDGET_NODE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_WIDGET_NODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_WIDGET_NODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_WIDGET_NODE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_WIDGET_NODE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_37A143E3CB638B52 ON icap__portfolio_widget_node (widget_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_37A143E3B96B5643 ON icap__portfolio_widget_node (portfolio_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id NUMBER(10) NOT NULL, 
                widget_node_id NUMBER(10) DEFAULT NULL, 
                city VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_WIDGET_USER_INFORMATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_WIDGET_USER_INFORMATION ADD CONSTRAINT ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_WIDGET_USER_INFORMATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_WIDGET_USER_INFORMATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2BFAA0348229816 ON icap__portfolio_widget_user_information (widget_node_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3CB638B52 FOREIGN KEY (widget_type_id) 
            REFERENCES icap__portfolio_widget_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD CONSTRAINT FK_E2BFAA0348229816 FOREIGN KEY (widget_node_id) 
            REFERENCES icap__portfolio_widget_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            DROP CONSTRAINT FK_3980F8F8B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP CONSTRAINT FK_37A143E3B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP CONSTRAINT FK_37A143E3CB638B52
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP CONSTRAINT FK_E2BFAA0348229816
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_type
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_node
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
    }
}