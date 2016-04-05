<?php

namespace Icap\WikiBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/28 02:22:20
 */
class Version20131028142219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wiki_contribution (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                section_id NUMBER(10) NOT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                creation_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__WIKI_CONTRIBUTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__WIKI_CONTRIBUTION ADD CONSTRAINT ICAP__WIKI_CONTRIBUTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__WIKI_CONTRIBUTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__WIKI_CONTRIBUTION_AI_PK BEFORE INSERT ON ICAP__WIKI_CONTRIBUTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__WIKI_CONTRIBUTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__WIKI_CONTRIBUTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__WIKI_CONTRIBUTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__WIKI_CONTRIBUTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_781E6502A76ED395 ON icap__wiki_contribution (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_781E6502D823E37A ON icap__wiki_contribution (section_id)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502D823E37A FOREIGN KEY (section_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD (
                active_contribution_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP (title, text, modification_date)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_82904AAFE665925 ON icap__wiki_section (active_contribution_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AAFE665925
        ");
        $this->addSql("
            DROP TABLE icap__wiki_contribution
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD (
                title VARCHAR2(255) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                modification_date TIMESTAMP(0) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP (active_contribution_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_82904AAFE665925
        ");
    }
}