<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 05:03:26
 */
class Version20150211170325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_panel_facet (
                id NUMBER(10) NOT NULL, 
                facet_id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                position NUMBER(10) NOT NULL, 
                isDefaultCollapsed NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_PANEL_FACET' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_PANEL_FACET ADD CONSTRAINT CLARO_PANEL_FACET_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_PANEL_FACET_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_PANEL_FACET_AI_PK BEFORE INSERT ON CLARO_PANEL_FACET FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_PANEL_FACET_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_PANEL_FACET_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_PANEL_FACET_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_PANEL_FACET_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_DA3985FFC889F24 ON claro_panel_facet (facet_id)
        ");
        $this->addSql("
            ALTER TABLE claro_panel_facet 
            ADD CONSTRAINT FK_DA3985FFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            DROP INDEX IDX_F6C21DB2FC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet RENAME COLUMN facet_id TO panelFacet_id
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2E99038C0 ON claro_field_facet (panelFacet_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT FK_F6C21DB2E99038C0
        ");
        $this->addSql("
            DROP TABLE claro_panel_facet
        ");
        $this->addSql("
            DROP INDEX IDX_F6C21DB2E99038C0
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet RENAME COLUMN panelfacet_id TO facet_id
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
    }
}