<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/21 11:28:05
 */
class Version20140321112802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_public_profile_preferences (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                share_policy NUMBER(10) NOT NULL, 
                display_phone_number NUMBER(1) NOT NULL, 
                display_email NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_USER_PUBLIC_PROFILE_PREFERENCES' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_USER_PUBLIC_PROFILE_PREFERENCES ADD CONSTRAINT CLARO_USER_PUBLIC_PROFILE_PREFERENCES_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_USER_PUBLIC_PROFILE_PREFERENCES_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_USER_PUBLIC_PROFILE_PREFERENCES_AI_PK BEFORE INSERT ON CLARO_USER_PUBLIC_PROFILE_PREFERENCES FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_USER_PUBLIC_PROFILE_PREFERENCES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_USER_PUBLIC_PROFILE_PREFERENCES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_USER_PUBLIC_PROFILE_PREFERENCES_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_USER_PUBLIC_PROFILE_PREFERENCES_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5CF2A583A76ED395 ON claro_user_public_profile_preferences (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD CONSTRAINT FK_5CF2A583A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD (
                public_url VARCHAR2(255) DEFAULT NULL, 
                has_tuned_public_url NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_public_profile_preferences
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP (
                public_url, has_tuned_public_url
            )
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
    }
}