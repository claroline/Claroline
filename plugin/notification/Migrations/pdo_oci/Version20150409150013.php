<?php

namespace Icap\NotificationBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/09 03:00:14
 */
class Version20150409150013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__notification_user_parameters (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                display_enabled_types CLOB NOT NULL, 
                rss_enabled_types CLOB NOT NULL, 
                rss_id VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__NOTIFICATION_USER_PARAMETERS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__NOTIFICATION_USER_PARAMETERS ADD CONSTRAINT ICAP__NOTIFICATION_USER_PARAMETERS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE ICAP__NOTIFICATION_USER_PARAMETERS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER ICAP__NOTIFICATION_USER_PARAMETERS_AI_PK BEFORE INSERT ON ICAP__NOTIFICATION_USER_PARAMETERS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__NOTIFICATION_USER_PARAMETERS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__NOTIFICATION_USER_PARAMETERS_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__NOTIFICATION_USER_PARAMETERS_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__NOTIFICATION_USER_PARAMETERS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F44A756DA9D08426 ON icap__notification_user_parameters (rss_id)
        ');
        $this->addSql("
            COMMENT ON COLUMN icap__notification_user_parameters.display_enabled_types IS '(DC2Type:array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN icap__notification_user_parameters.rss_enabled_types IS '(DC2Type:array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__notification_user_parameters
        ');
    }
}
