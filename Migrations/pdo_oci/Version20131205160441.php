<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/05 04:04:44
 */
class Version20131205160441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_api_client (
                id NUMBER(10) NOT NULL, 
                random_id VARCHAR2(255) NOT NULL, 
                redirect_uris CLOB NOT NULL, 
                secret VARCHAR2(255) NOT NULL, 
                allowed_grant_types CLOB NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_API_CLIENT' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_API_CLIENT ADD CONSTRAINT CLARO_API_CLIENT_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_API_CLIENT_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_API_CLIENT_AI_PK BEFORE INSERT ON CLARO_API_CLIENT FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_API_CLIENT_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_API_CLIENT_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_API_CLIENT_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_API_CLIENT_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_api_client.redirect_uris IS '(DC2Type:array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_api_client.allowed_grant_types IS '(DC2Type:array)'
        ");
        $this->addSql("
            CREATE TABLE claro_api_access_token (
                id NUMBER(10) NOT NULL, 
                client_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                token VARCHAR2(255) NOT NULL, 
                expires_at NUMBER(10) DEFAULT NULL, 
                scope VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_API_ACCESS_TOKEN' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_API_ACCESS_TOKEN ADD CONSTRAINT CLARO_API_ACCESS_TOKEN_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_API_ACCESS_TOKEN_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_API_ACCESS_TOKEN_AI_PK BEFORE INSERT ON CLARO_API_ACCESS_TOKEN FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_API_ACCESS_TOKEN_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_API_ACCESS_TOKEN_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_API_ACCESS_TOKEN_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_API_ACCESS_TOKEN_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE948285F37A13B ON claro_api_access_token (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE9482819EB6921 ON claro_api_access_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE94828A76ED395 ON claro_api_access_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_refresh_token (
                id NUMBER(10) NOT NULL, 
                client_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                token VARCHAR2(255) NOT NULL, 
                expires_at NUMBER(10) DEFAULT NULL, 
                scope VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_API_REFRESH_TOKEN' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_API_REFRESH_TOKEN ADD CONSTRAINT CLARO_API_REFRESH_TOKEN_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_API_REFRESH_TOKEN_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_API_REFRESH_TOKEN_AI_PK BEFORE INSERT ON CLARO_API_REFRESH_TOKEN FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_API_REFRESH_TOKEN_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_API_REFRESH_TOKEN_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_API_REFRESH_TOKEN_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_API_REFRESH_TOKEN_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B1292B905F37A13B ON claro_api_refresh_token (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B9019EB6921 ON claro_api_refresh_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B90A76ED395 ON claro_api_refresh_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_auth_code (
                id NUMBER(10) NOT NULL, 
                client_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                token VARCHAR2(255) NOT NULL, 
                redirect_uri CLOB NOT NULL, 
                expires_at NUMBER(10) DEFAULT NULL, 
                scope VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_API_AUTH_CODE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_API_AUTH_CODE ADD CONSTRAINT CLARO_API_AUTH_CODE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_API_AUTH_CODE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_API_AUTH_CODE_AI_PK BEFORE INSERT ON CLARO_API_AUTH_CODE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_API_AUTH_CODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_API_AUTH_CODE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_API_AUTH_CODE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_API_AUTH_CODE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_9DFA4575F37A13B ON claro_api_auth_code (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA45719EB6921 ON claro_api_auth_code (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA457A76ED395 ON claro_api_auth_code (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_access_token 
            ADD CONSTRAINT FK_CE9482819EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_access_token 
            ADD CONSTRAINT FK_CE94828A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token 
            ADD CONSTRAINT FK_B1292B9019EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token 
            ADD CONSTRAINT FK_B1292B90A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code 
            ADD CONSTRAINT FK_9DFA45719EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code 
            ADD CONSTRAINT FK_9DFA457A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_api_access_token 
            DROP CONSTRAINT FK_CE9482819EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token 
            DROP CONSTRAINT FK_B1292B9019EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code 
            DROP CONSTRAINT FK_9DFA45719EB6921
        ");
        $this->addSql("
            DROP TABLE claro_api_client
        ");
        $this->addSql("
            DROP TABLE claro_api_access_token
        ");
        $this->addSql("
            DROP TABLE claro_api_refresh_token
        ");
        $this->addSql("
            DROP TABLE claro_api_auth_code
        ");
    }
}