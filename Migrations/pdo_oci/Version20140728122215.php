<?php

namespace Claroline\SurveyBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 12:22:17
 */
class Version20140728122215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_question_type (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SURVEY_QUESTION_TYPE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SURVEY_QUESTION_TYPE ADD CONSTRAINT CLARO_SURVEY_QUESTION_TYPE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SURVEY_QUESTION_TYPE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SURVEY_QUESTION_TYPE_AI_PK BEFORE INSERT ON CLARO_SURVEY_QUESTION_TYPE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SURVEY_QUESTION_TYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SURVEY_QUESTION_TYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SURVEY_QUESTION_TYPE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SURVEY_QUESTION_TYPE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD (
                question_type_id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD CONSTRAINT FK_5E6CE963CB90598E FOREIGN KEY (question_type_id) 
            REFERENCES claro_survey_question_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E6CE963CB90598E ON claro_survey (question_type_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey 
            DROP CONSTRAINT FK_5E6CE963CB90598E
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_type
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            DROP (question_type_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5E6CE963CB90598E
        ");
    }
}