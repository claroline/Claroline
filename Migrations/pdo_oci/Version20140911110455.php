<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 11:04:57
 */
class Version20140911110455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_badge_usage_config (
                id NUMBER(10) NOT NULL, 
                numberLastAwardedBadge NUMBER(5) NOT NULL, 
                numberMostAwardedBadge NUMBER(5) NOT NULL, 
                widgetInstance_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_WIDGET_BADGE_USAGE_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_WIDGET_BADGE_USAGE_CONFIG ADD CONSTRAINT CLARO_WIDGET_BADGE_USAGE_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_WIDGET_BADGE_USAGE_CONFIG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_WIDGET_BADGE_USAGE_CONFIG_AI_PK BEFORE INSERT ON CLARO_WIDGET_BADGE_USAGE_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_WIDGET_BADGE_USAGE_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_WIDGET_BADGE_USAGE_CONFIG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_WIDGET_BADGE_USAGE_CONFIG_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_WIDGET_BADGE_USAGE_CONFIG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_9A2EA78BAB7B5A55 ON claro_widget_badge_usage_config (widgetInstance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_badge_usage_config 
            ADD CONSTRAINT FK_9A2EA78BAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD (
                authentication VARCHAR2(255) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_badge_usage_config
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP (authentication)
        ");
    }
}