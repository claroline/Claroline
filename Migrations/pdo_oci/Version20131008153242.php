<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/08 03:32:45
 */
class Version20131008153242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id NUMBER(10) NOT NULL, 
                badge_id NUMBER(10) NOT NULL, 
                occurrence NUMBER(5) NOT NULL, 
                action VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_BADGE_RULE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_BADGE_RULE ADD CONSTRAINT CLARO_BADGE_RULE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_BADGE_RULE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_BADGE_RULE_AI_PK BEFORE INSERT ON CLARO_BADGE_RULE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_BADGE_RULE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_BADGE_RULE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_BADGE_RULE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_BADGE_RULE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            ADD CONSTRAINT FK_849BC831F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD (
                workspace_id NUMBER(10) DEFAULT NULL, 
                automatic_award NUMBER(1) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP (workspace_id, automatic_award)
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP CONSTRAINT FK_74F39F0F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            DROP CONSTRAINT FK_487A496AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            DROP CONSTRAINT FK_487A496AF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            DROP CONSTRAINT FK_849BC831F7A2C2FC
        ");
    }
}