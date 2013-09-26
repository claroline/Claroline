<?php

namespace Innova\PathBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/26 09:17:31
 */
class Version20130926091730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2excludedResource (
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
            WHERE TABLE_NAME = 'INNOVA_STEP2EXCLUDEDRESOURCE'
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_STEP2EXCLUDEDRESOURCE ADD CONSTRAINT INNOVA_STEP2EXCLUDEDRESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_STEP2EXCLUDEDRESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_STEP2EXCLUDEDRESOURCE_AI_PK BEFORE INSERT ON INNOVA_STEP2EXCLUDEDRESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
            SELECT INNOVA_STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
            FROM DUAL; IF (
                : NEW.ID IS NULL
                OR : NEW.ID = 0
            ) THEN
            SELECT INNOVA_STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
            FROM DUAL; ELSE
            SELECT NVL(Last_Number, 0) INTO last_Sequence
            FROM User_Sequences
            WHERE Sequence_Name = 'INNOVA_STEP2EXCLUDEDRESOURCE_ID_SEQ';
            SELECT : NEW.ID INTO last_InsertID
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
            SELECT INNOVA_STEP2EXCLUDEDRESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_4CBCF07C73B21E9C ON innova_step2excludedResource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4CBCF07CB87FAB32 ON innova_step2excludedResource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResource
            ADD CONSTRAINT FK_4CBCF07C73B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResource
            ADD CONSTRAINT FK_4CBCF07CB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2excludedResource
        ");
    }
}
