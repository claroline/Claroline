<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 02:40:12
 */
class Version20150311144010 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL NULL, 
                user_id NUMBER(10) DEFAULT NULL NULL, 
                widget_instance_id NUMBER(10) NOT NULL, 
                row_position NUMBER(10) NOT NULL, 
                column_position NUMBER(10) NOT NULL, 
                widget_width NUMBER(10) DEFAULT 4 NOT NULL, 
                widget_height NUMBER(10) DEFAULT 3 NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_WIDGET_DISPLAY_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_WIDGET_DISPLAY_CONFIG ADD CONSTRAINT CLARO_WIDGET_DISPLAY_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_WIDGET_DISPLAY_CONFIG_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_WIDGET_DISPLAY_CONFIG_AI_PK BEFORE INSERT ON CLARO_WIDGET_DISPLAY_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_WIDGET_DISPLAY_CONFIG_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_WIDGET_DISPLAY_CONFIG_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_WIDGET_DISPLAY_CONFIG_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_WIDGET_DISPLAY_CONFIG_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497282D40A1F ON claro_widget_display_config (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE4972A76ED395 ON claro_widget_display_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497244BF891 ON claro_widget_display_config (widget_instance_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_user ON claro_widget_display_config (widget_instance_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_workspace ON claro_widget_display_config (
                widget_instance_id, workspace_id
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE4972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497244BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD (
                default_width NUMBER(10) DEFAULT 4 NOT NULL, 
                default_height NUMBER(10) DEFAULT 3 NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP (default_width, default_height)
        ");
    }
}