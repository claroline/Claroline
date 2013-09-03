<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:56:21
 */
class Version20130903095620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id NUMBER(10) NOT NULL, 
                resource_type_id NUMBER(10) DEFAULT NULL, 
                name VARCHAR2(255) DEFAULT NULL, 
                async NUMBER(1) DEFAULT NULL, 
                is_custom NUMBER(1) NOT NULL, 
                is_form NUMBER(1) NOT NULL, 
                permRequired VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MENU_ACTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MENU_ACTION ADD CONSTRAINT CLARO_MENU_ACTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MENU_ACTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MENU_ACTION_AI_PK BEFORE INSERT ON CLARO_MENU_ACTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MENU_ACTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_MENU_ACTION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MENU_ACTION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MENU_ACTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_1F57E52B98EC6B7B ON claro_menu_action (resource_type_id)
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
    }
}