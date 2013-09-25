<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 06:44:25
 */
class Version20130925184425 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE Step2ExcludedResource (
                id NUMBER(10) NOT NULL,
                step_id NUMBER(10) DEFAULT NULL,
                resourceNode_id NUMBER(10) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
            FROM USER_CONSTRAINTS
            WHERE TABLE_NAME = 'STEP2EXCLUDEDRESOURCE'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE STEP2EXCLUDEDRESOURCE ADD CONSTRAINT STEP2EXCLUDEDRESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE STEP2EXCLUDEDRESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER STEP2EXCLUDEDRESOURCE_AI_PK BEFORE INSERT ON STEP2EXCLUDEDRESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
            FROM DUAL; IF (
                : NEW.ID IS NULL
                OR : NEW.ID = 0
            ) THEN
            SELECT STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'STEP2EXCLUDEDRESOURCE_ID_SEQ';
            SELECT : NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E173B21E9C ON Step2ExcludedResource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E1B87FAB32 ON Step2ExcludedResource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E173B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E1B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE Step2ExcludedResource
        ");
    }
}
