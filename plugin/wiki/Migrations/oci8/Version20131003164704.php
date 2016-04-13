<?php

namespace Icap\WikiBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/10/03 04:47:05
 */
class Version20131003164704 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__wiki_section (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                wiki_id NUMBER(10) NOT NULL, 
                parent_id NUMBER(10) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                visible NUMBER(1) NOT NULL, 
                text CLOB DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                modification_date TIMESTAMP(0) NOT NULL, 
                lft NUMBER(10) NOT NULL, 
                lvl NUMBER(10) NOT NULL, 
                rgt NUMBER(10) NOT NULL, 
                root NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WIKI_SECTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WIKI_SECTION ADD CONSTRAINT ICAP__WIKI_SECTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE ICAP__WIKI_SECTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER ICAP__WIKI_SECTION_AI_PK BEFORE INSERT ON ICAP__WIKI_SECTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WIKI_SECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WIKI_SECTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WIKI_SECTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WIKI_SECTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql('
            CREATE INDEX IDX_82904AAA76ED395 ON icap__wiki_section (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_82904AAAA948DBE ON icap__wiki_section (wiki_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_82904AA727ACA70 ON icap__wiki_section (parent_id)
        ');
        $this->addSql('
            CREATE TABLE icap__wiki (
                id NUMBER(10) NOT NULL, 
                root_id NUMBER(10) DEFAULT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WIKI' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WIKI ADD CONSTRAINT ICAP__WIKI_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE ICAP__WIKI_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER ICAP__WIKI_AI_PK BEFORE INSERT ON ICAP__WIKI FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WIKI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WIKI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WIKI_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WIKI_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1FAD6B8179066886 ON icap__wiki (root_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 ON icap__wiki (resourceNode_id)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAAA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wiki (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AA727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B8179066886 FOREIGN KEY (root_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B81B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AA727ACA70
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            DROP CONSTRAINT FK_1FAD6B8179066886
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AAAA948DBE
        ');
        $this->addSql('
            DROP TABLE icap__wiki_section
        ');
        $this->addSql('
            DROP TABLE icap__wiki
        ');
    }
}
