<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/06 10:25:07
 */
class Version20130806102506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_announcement (
                id NUMBER(10) NOT NULL, 
                creator_id NUMBER(10) NOT NULL, 
                aggregate_id NUMBER(10) NOT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                content VARCHAR2(1023) DEFAULT NULL, 
                announcer VARCHAR2(255) DEFAULT NULL, 
                publication_date TIMESTAMP(0) DEFAULT NULL, 
                visible NUMBER(1) NOT NULL, 
                visible_from TIMESTAMP(0) DEFAULT NULL, 
                visible_until TIMESTAMP(0) DEFAULT NULL, 
                \"order\" NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ANNOUNCEMENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ANNOUNCEMENT ADD CONSTRAINT CLARO_ANNOUNCEMENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ANNOUNCEMENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ANNOUNCEMENT_AI_PK BEFORE INSERT ON CLARO_ANNOUNCEMENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ANNOUNCEMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_ANNOUNCEMENT_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ANNOUNCEMENT_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ANNOUNCEMENT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E361220EA6 ON claro_announcement (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E3D0BBCCBE ON claro_announcement (aggregate_id)
        ");
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E3D0BBCCBE FOREIGN KEY (aggregate_id) 
            REFERENCES claro_announcement_aggregate (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_announcement
        ");
    }
}