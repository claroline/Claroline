<?php

namespace Innova\PathBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 03:56:41
 */
class Version20131017155640 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_nonDigitalResourceType (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'INNOVA_NONDIGITALRESOURCETYPE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE INNOVA_NONDIGITALRESOURCETYPE ADD CONSTRAINT INNOVA_NONDIGITALRESOURCETYPE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE INNOVA_NONDIGITALRESOURCETYPE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER INNOVA_NONDIGITALRESOURCETYPE_AI_PK BEFORE INSERT ON INNOVA_NONDIGITALRESOURCETYPE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT INNOVA_NONDIGITALRESOURCETYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT INNOVA_NONDIGITALRESOURCETYPE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'INNOVA_NONDIGITALRESOURCETYPE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT INNOVA_NONDIGITALRESOURCETYPE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD (
                nonDigitalResourceType_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            DROP (type)
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD CONSTRAINT FK_305E9E568CF60863 FOREIGN KEY (nonDigitalResourceType_id) 
            REFERENCES innova_nonDigitalResourceType (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_305E9E568CF60863 ON innova_nonDigitalResource (nonDigitalResourceType_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            DROP CONSTRAINT FK_305E9E568CF60863
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResourceType
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD (
                type VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            DROP (nonDigitalResourceType_id)
        ");
        $this->addSql("
            DROP INDEX IDX_305E9E568CF60863
        ");
    }
}