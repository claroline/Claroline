<?php

namespace Icap\NotificationBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/01/28 08:56:27
 */
class Version20140128085626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            '
                        CREATE TABLE icap__notification_follower_resource (
                            id NUMBER(10) NOT NULL,
                            hash VARCHAR2(64) NOT NULL,
                            resource_class VARCHAR2(255) NOT NULL,
                            resource_id NUMBER(10) NOT NULL,
                            follower_id NUMBER(10) NOT NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            "
                        DECLARE constraints_Count NUMBER; BEGIN
                        SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
                        FROM USER_CONSTRAINTS
                        WHERE TABLE_NAME = 'ICAP__NOTIFICATION_FOLLOWER_RESOURCE'
                        AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
                        OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__NOTIFICATION_FOLLOWER_RESOURCE ADD CONSTRAINT ICAP__NOTIFICATION_FOLLOWER_RESOURCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
                    "
        );
        $this->addSql(
            '
                        CREATE SEQUENCE ICAP__NOTIFICATION_FOLLOWER_RESOURCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
                    '
        );
        $this->addSql(
            "
                        CREATE TRIGGER ICAP__NOTIFICATION_FOLLOWER_RESOURCE_AI_PK BEFORE INSERT ON ICAP__NOTIFICATION_FOLLOWER_RESOURCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
                        SELECT ICAP__NOTIFICATION_FOLLOWER_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; IF (
                            : NEW.ID IS NULL
                            OR : NEW.ID = 0
                        ) THEN
                        SELECT ICAP__NOTIFICATION_FOLLOWER_RESOURCE_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; ELSE
                        SELECT NVL(Last_Number, 0) INTO last_Sequence
                        FROM User_Sequences
                        WHERE Sequence_Name = 'ICAP__NOTIFICATION_FOLLOWER_RESOURCE_ID_SEQ';
                        SELECT : NEW.ID INTO last_InsertID
                        FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
                        SELECT ICAP__NOTIFICATION_FOLLOWER_RESOURCE_ID_SEQ.NEXTVAL INTO last_Sequence
                        FROM DUAL; END LOOP; END IF; END;
                    "
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification (
                            id NUMBER(10) NOT NULL,
                            creation_date TIMESTAMP(0) NOT NULL,
                            user_id NUMBER(10) DEFAULT NULL NULL,
                            resource_id NUMBER(10) DEFAULT NULL NULL,
                            icon_key VARCHAR2(255) DEFAULT NULL NULL,
                            action_key VARCHAR2(255) NOT NULL,
                            details CLOB DEFAULT NULL NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            "
                        DECLARE constraints_Count NUMBER; BEGIN
                        SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
                        FROM USER_CONSTRAINTS
                        WHERE TABLE_NAME = 'ICAP__NOTIFICATION'
                        AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
                        OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__NOTIFICATION ADD CONSTRAINT ICAP__NOTIFICATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
                    "
        );
        $this->addSql(
            '
                        CREATE SEQUENCE ICAP__NOTIFICATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
                    '
        );
        $this->addSql(
            "
                        CREATE TRIGGER ICAP__NOTIFICATION_AI_PK BEFORE INSERT ON ICAP__NOTIFICATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
                        SELECT ICAP__NOTIFICATION_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; IF (
                            : NEW.ID IS NULL
                            OR : NEW.ID = 0
                        ) THEN
                        SELECT ICAP__NOTIFICATION_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; ELSE
                        SELECT NVL(Last_Number, 0) INTO last_Sequence
                        FROM User_Sequences
                        WHERE Sequence_Name = 'ICAP__NOTIFICATION_ID_SEQ';
                        SELECT : NEW.ID INTO last_InsertID
                        FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
                        SELECT ICAP__NOTIFICATION_ID_SEQ.NEXTVAL INTO last_Sequence
                        FROM DUAL; END LOOP; END IF; END;
                    "
        );
        $this->addSql(
            "
                        COMMENT ON COLUMN icap__notification.details IS '(DC2Type:json_array)'
                    "
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification_viewer (
                            id NUMBER(10) NOT NULL,
                            notification_id NUMBER(10) NOT NULL,
                            viewer_id NUMBER(10) NOT NULL,
                            status NUMBER(1) DEFAULT NULL NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            "
                        DECLARE constraints_Count NUMBER; BEGIN
                        SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count
                        FROM USER_CONSTRAINTS
                        WHERE TABLE_NAME = 'ICAP__NOTIFICATION_VIEWER'
                        AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0
                        OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__NOTIFICATION_VIEWER ADD CONSTRAINT ICAP__NOTIFICATION_VIEWER_AI_PK PRIMARY KEY (ID)'; END IF; END;
                    "
        );
        $this->addSql(
            '
                        CREATE SEQUENCE ICAP__NOTIFICATION_VIEWER_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
                    '
        );
        $this->addSql(
            "
                        CREATE TRIGGER ICAP__NOTIFICATION_VIEWER_AI_PK BEFORE INSERT ON ICAP__NOTIFICATION_VIEWER FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN
                        SELECT ICAP__NOTIFICATION_VIEWER_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; IF (
                            : NEW.ID IS NULL
                            OR : NEW.ID = 0
                        ) THEN
                        SELECT ICAP__NOTIFICATION_VIEWER_ID_SEQ.NEXTVAL INTO : NEW.ID
                        FROM DUAL; ELSE
                        SELECT NVL(Last_Number, 0) INTO last_Sequence
                        FROM User_Sequences
                        WHERE Sequence_Name = 'ICAP__NOTIFICATION_VIEWER_ID_SEQ';
                        SELECT : NEW.ID INTO last_InsertID
                        FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP
                        SELECT ICAP__NOTIFICATION_VIEWER_ID_SEQ.NEXTVAL INTO last_Sequence
                        FROM DUAL; END LOOP; END IF; END;
                    "
        );
        $this->addSql(
            '
                        CREATE INDEX IDX_DB60418BEF1A9D84 ON icap__notification_viewer (notification_id)
                    '
        );
        $this->addSql(
            '
                        ALTER TABLE icap__notification_viewer
                        ADD CONSTRAINT FK_DB60418BEF1A9D84 FOREIGN KEY (notification_id)
                        REFERENCES icap__notification (id)
                        ON DELETE CASCADE
                    '
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            '
                        ALTER TABLE icap__notification_viewer
                        DROP CONSTRAINT FK_DB60418BEF1A9D84
                    '
        );
        $this->addSql(
            '
                        DROP TABLE icap__notification_follower_resource
                    '
        );
        $this->addSql(
            '
                        DROP TABLE icap__notification
                    '
        );
        $this->addSql(
            '
                        DROP TABLE icap__notification_viewer
                    '
        );
    }
}
