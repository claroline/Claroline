<?php

namespace HeVinci\CompetencyBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/25 05:09:55
 */
class Version20150325170954 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id NUMBER(10) NOT NULL, 
                objective_id NUMBER(10) NOT NULL, 
                competency_id NUMBER(10) NOT NULL, 
                level_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_OBJECTIVE_COMPETENCY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_OBJECTIVE_COMPETENCY ADD CONSTRAINT HEVINCI_OBJECTIVE_COMPETENCY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE HEVINCI_OBJECTIVE_COMPETENCY_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER HEVINCI_OBJECTIVE_COMPETENCY_AI_PK BEFORE INSERT ON HEVINCI_OBJECTIVE_COMPETENCY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_OBJECTIVE_COMPETENCY_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_OBJECTIVE_COMPETENCY_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854473484933 ON hevinci_objective_competency (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF8544FB9F58C ON hevinci_objective_competency (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF85445FB14BA7 ON hevinci_objective_competency (level_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
    }
}