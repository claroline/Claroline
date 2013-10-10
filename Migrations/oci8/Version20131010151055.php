<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/10 03:10:55
 */
class Version20131010151055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_log_widget_config (
                id NUMBER(10) NOT NULL, 
                amount NUMBER(10) NOT NULL, 
                restrictions CLOB DEFAULT NULL, 
                widgetInstance_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_LOG_WIDGET_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_LOG_WIDGET_CONFIG ADD CONSTRAINT CLARO_LOG_WIDGET_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_LOG_WIDGET_CONFIG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_LOG_WIDGET_CONFIG_AI_PK BEFORE INSERT ON CLARO_LOG_WIDGET_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_LOG_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_LOG_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_LOG_WIDGET_CONFIG_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_LOG_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C16334B2AB7B5A55 ON claro_log_widget_config (widgetInstance_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_log_widget_config.restrictions IS '(DC2Type:simple_array)'
        ");
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
            CREATE TABLE claro_simple_text_widget_config (
                id NUMBER(10) NOT NULL, 
                content CLOB NOT NULL, 
                widgetInstance_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_SIMPLE_TEXT_WIDGET_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_SIMPLE_TEXT_WIDGET_CONFIG ADD CONSTRAINT CLARO_SIMPLE_TEXT_WIDGET_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_SIMPLE_TEXT_WIDGET_CONFIG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_SIMPLE_TEXT_WIDGET_CONFIG_AI_PK BEFORE INSERT ON CLARO_SIMPLE_TEXT_WIDGET_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_SIMPLE_TEXT_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_SIMPLE_TEXT_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_SIMPLE_TEXT_WIDGET_CONFIG_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_SIMPLE_TEXT_WIDGET_CONFIG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C389EBCCAB7B5A55 ON claro_simple_text_widget_config (widgetInstance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_widget_config 
            ADD CONSTRAINT FK_C16334B2AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_simple_text_widget_config 
            ADD CONSTRAINT FK_C389EBCCAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD (
                picture VARCHAR2(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL
            )
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
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD (
                is_displayable_in_workspace NUMBER(1) NOT NULL, 
                is_displayable_in_desktop NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD (
                widget_instance_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP (widget_id)
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_widget_config 
            DROP CONSTRAINT FK_C16334B2AB7B5A55
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE claro_simple_text_widget_config 
            DROP CONSTRAINT FK_C389EBCCAB7B5A55
        ");
        $this->addSql("
            DROP TABLE claro_log_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            DROP TABLE claro_simple_text_widget_config
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
            ALTER TABLE claro_user 
            DROP (picture, description)
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP (
                is_displayable_in_workspace, is_displayable_in_desktop
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD (
                widget_id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP (widget_instance_id)
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
    }
}