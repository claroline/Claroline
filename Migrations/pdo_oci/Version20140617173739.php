<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/17 05:37:40
 */
class Version20140617173739 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_public_profile_preference (
                id NUMBER(10) NOT NULL, 
                role_id NUMBER(10) NOT NULL, 
                baseData NUMBER(1) NOT NULL, 
                mail NUMBER(1) NOT NULL, 
                phone NUMBER(1) NOT NULL, 
                sendMail NUMBER(1) NOT NULL, 
                sendMessage NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_PUBLIC_PROFILE_PREFERENCE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_PUBLIC_PROFILE_PREFERENCE ADD CONSTRAINT CLARO_PUBLIC_PROFILE_PREFERENCE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_PUBLIC_PROFILE_PREFERENCE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_PUBLIC_PROFILE_PREFERENCE_AI_PK BEFORE INSERT ON CLARO_PUBLIC_PROFILE_PREFERENCE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_PUBLIC_PROFILE_PREFERENCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_PUBLIC_PROFILE_PREFERENCE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_PUBLIC_PROFILE_PREFERENCE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_PUBLIC_PROFILE_PREFERENCE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C73878F1D60322AC ON claro_public_profile_preference (role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_public_profile_preference 
            ADD CONSTRAINT FK_C73878F1D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_public_profile_preference
        ");
    }
}