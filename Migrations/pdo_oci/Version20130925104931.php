<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 10:49:32
 */
class Version20130925104931 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                widget_id NUMBER(10) NOT NULL, 
                is_admin NUMBER(1) NOT NULL, 
                is_desktop NUMBER(1) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_WIDGET_INSTANCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_WIDGET_INSTANCE ADD CONSTRAINT CLARO_WIDGET_INSTANCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_WIDGET_INSTANCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_WIDGET_INSTANCE_AI_PK BEFORE INSERT ON CLARO_WIDGET_INSTANCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_WIDGET_INSTANCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_WIDGET_INSTANCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_WIDGET_INSTANCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_WIDGET_INSTANCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A38582D40A1F ON claro_widget_instance (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385A76ED395 ON claro_widget_instance (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385FBE885E2 ON claro_widget_instance (widget_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A38582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config RENAME COLUMN workspace_id TO displayConfig_id
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP (is_default)
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (displayConfig_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config RENAME COLUMN widget_id TO widget_instance_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EFBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD (
                is_displayable_in_workspace NUMBER(1) NOT NULL, 
                is_displayable_in_desktop NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED3EF00646E
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23E44BF891
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP (
                is_displayable_in_workspace, is_displayable_in_desktop
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config RENAME COLUMN widget_instance_id TO widget_id
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23EFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config (widget_id)
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD (
                is_default NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config RENAME COLUMN displayconfig_id TO workspace_id
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED3EF00646E
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }
}