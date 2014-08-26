<?php

namespace Icap\WebsiteBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/07 03:19:14
 */
class Version20140707151913 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__website_page (
                id NUMBER(10) NOT NULL, 
                resource_node_id NUMBER(10) DEFAULT NULL, 
                website_id NUMBER(10) NOT NULL, 
                parent_id NUMBER(10) DEFAULT NULL, 
                visible NUMBER(1) NOT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                title VARCHAR2(255) NOT NULL, 
                richText CLOB DEFAULT NULL, 
                url VARCHAR2(255) DEFAULT NULL, 
                isSection NUMBER(1) NOT NULL, 
                description VARCHAR2(255) DEFAULT NULL, 
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
            WHERE TABLE_NAME = 'ICAP__WEBSITE_PAGE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WEBSITE_PAGE ADD CONSTRAINT ICAP__WEBSITE_PAGE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WEBSITE_PAGE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WEBSITE_PAGE_AI_PK BEFORE INSERT ON ICAP__WEBSITE_PAGE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WEBSITE_PAGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WEBSITE_PAGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WEBSITE_PAGE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WEBSITE_PAGE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D41BAD783F ON icap__website_page (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D418F45C82 ON icap__website_page (website_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D4727ACA70 ON icap__website_page (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__website_options (
                id NUMBER(10) NOT NULL, 
                website_id NUMBER(10) DEFAULT NULL, 
                copyrightEnabled NUMBER(1) NOT NULL, 
                copyrightText VARCHAR2(255) DEFAULT NULL, 
                analyticsProvider VARCHAR2(255) DEFAULT NULL, 
                analyticsAccountId VARCHAR2(255) DEFAULT NULL, 
                cssCode CLOB DEFAULT NULL, 
                bgColor VARCHAR2(255) DEFAULT NULL, 
                bgImage VARCHAR2(255) DEFAULT NULL, 
                bgRepeat VARCHAR2(255) DEFAULT NULL, 
                bgPosition VARCHAR2(255) DEFAULT NULL, 
                bannerBgColor VARCHAR2(255) DEFAULT NULL, 
                bannerBgImage VARCHAR2(255) DEFAULT NULL, 
                bannerBgRepeat VARCHAR2(255) DEFAULT NULL, 
                bannerBgPosition VARCHAR2(255) DEFAULT NULL, 
                bannerHeight NUMBER(10) DEFAULT NULL, 
                bannerEnabled NUMBER(1) NOT NULL, 
                bannerText CLOB DEFAULT NULL, 
                footerBgColor VARCHAR2(255) DEFAULT NULL, 
                footerBgImage VARCHAR2(255) DEFAULT NULL, 
                footerBgRepeat VARCHAR2(255) DEFAULT NULL, 
                footerBgPosition VARCHAR2(255) DEFAULT NULL, 
                footerHeight NUMBER(10) DEFAULT NULL, 
                footerEnabled NUMBER(1) NOT NULL, 
                footerText CLOB DEFAULT NULL, 
                menuBgColor VARCHAR2(255) DEFAULT NULL, 
                sectionBgColor VARCHAR2(255) DEFAULT NULL, 
                menuBorderColor VARCHAR2(255) DEFAULT NULL, 
                menuFontColor VARCHAR2(255) DEFAULT NULL, 
                menuHoverColor VARCHAR2(255) DEFAULT NULL, 
                menuFontFamily VARCHAR2(255) DEFAULT NULL, 
                menuFontStyle VARCHAR2(255) DEFAULT NULL, 
                menuFontWeight VARCHAR2(255) DEFAULT NULL, 
                menuWidth NUMBER(10) DEFAULT NULL, 
                menuOrientation VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WEBSITE_OPTIONS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WEBSITE_OPTIONS ADD CONSTRAINT ICAP__WEBSITE_OPTIONS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WEBSITE_OPTIONS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WEBSITE_OPTIONS_AI_PK BEFORE INSERT ON ICAP__WEBSITE_OPTIONS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WEBSITE_OPTIONS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WEBSITE_OPTIONS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WEBSITE_OPTIONS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WEBSITE_OPTIONS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C40F17718F45C82 ON icap__website_options (website_id)
        ");
        $this->addSql("
            CREATE TABLE icap__website (
                id NUMBER(10) NOT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WEBSITE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WEBSITE ADD CONSTRAINT ICAP__WEBSITE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WEBSITE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WEBSITE_AI_PK BEFORE INSERT ON ICAP__WEBSITE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WEBSITE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WEBSITE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WEBSITE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WEBSITE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8B87FAB32 ON icap__website (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D418F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D4727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website_options 
            ADD CONSTRAINT FK_C40F17718F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_page 
            DROP CONSTRAINT FK_FB66D1D4727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            DROP CONSTRAINT FK_FB66D1D418F45C82
        ");
        $this->addSql("
            ALTER TABLE icap__website_options 
            DROP CONSTRAINT FK_C40F17718F45C82
        ");
        $this->addSql("
            DROP TABLE icap__website_page
        ");
        $this->addSql("
            DROP TABLE icap__website_options
        ");
        $this->addSql("
            DROP TABLE icap__website
        ");
    }
}