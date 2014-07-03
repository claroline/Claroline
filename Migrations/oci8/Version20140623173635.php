<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 05:36:36
 */
class Version20140623173635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id NUMBER(10) NOT NULL, 
                role_id NUMBER(10) NOT NULL, 
                baseData NUMBER(1) NOT NULL, 
                mail NUMBER(1) NOT NULL, 
                phone NUMBER(1) NOT NULL, 
                sendMail NUMBER(1) NOT NULL, 
                sendMessage NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_GENERAL_FACET_PREFERENCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_GENERAL_FACET_PREFERENCE ADD CONSTRAINT CLARO_GENERAL_FACET_PREFERENCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_GENERAL_FACET_PREFERENCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_GENERAL_FACET_PREFERENCE_AI_PK BEFORE INSERT ON CLARO_GENERAL_FACET_PREFERENCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_GENERAL_FACET_PREFERENCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_GENERAL_FACET_PREFERENCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_GENERAL_FACET_PREFERENCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_GENERAL_FACET_PREFERENCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN last_date TO evaluation_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN last_date TO lastest_evaluation_date
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN lastest_evaluation_date TO last_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN evaluation_date TO last_date
        ");
    }
}