<?php

namespace Icap\NotificationBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/14 03:39:15
 */
class Version20150414153912 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__notification_plugin_configuration (
                id NUMBER(10) NOT NULL, 
                dropdown_items NUMBER(10) NOT NULL, 
                max_per_page NUMBER(10) NOT NULL, 
                purge_enabled NUMBER(1) NOT NULL, 
                purge_after_days NUMBER(10) NOT NULL, 
                last_purge_date TIMESTAMP(0) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__NOTIFICATION_PLUGIN_CONFIGURATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__NOTIFICATION_PLUGIN_CONFIGURATION ADD CONSTRAINT ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_AI_PK BEFORE INSERT ON ICAP__NOTIFICATION_PLUGIN_CONFIGURATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__NOTIFICATION_PLUGIN_CONFIGURATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__notification_plugin_configuration
        ');
    }
}
