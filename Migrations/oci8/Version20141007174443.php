<?php

namespace Claroline\TeamBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/07 05:44:45
 */
class Version20141007174443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_team (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) NOT NULL, 
                role_id NUMBER(10) DEFAULT NULL, 
                team_manager NUMBER(10) DEFAULT NULL, 
                team_manager_role NUMBER(10) DEFAULT NULL, 
                directory_id NUMBER(10) DEFAULT NULL, 
                name VARCHAR2(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                max_users NUMBER(10) DEFAULT NULL, 
                self_registration NUMBER(1) NOT NULL, 
                self_unregistration NUMBER(1) NOT NULL, 
                is_public NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_TEAM' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_TEAM ADD CONSTRAINT CLARO_TEAM_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_TEAM_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_TEAM_AI_PK BEFORE INSERT ON CLARO_TEAM FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_TEAM_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_TEAM_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_TEAM_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_TEAM_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580482D40A1F ON claro_team (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE5804D60322AC ON claro_team (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580455D548E ON claro_team (team_manager)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE580459E625D1 ON claro_team (team_manager_role)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE58042C94069F ON claro_team (directory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_users (
                team_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(team_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3296CD8AE ON claro_team_users (team_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3A76ED395 ON claro_team_users (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_parameters (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) NOT NULL, 
                self_registration NUMBER(1) NOT NULL, 
                self_unregistration NUMBER(1) NOT NULL, 
                is_public NUMBER(1) NOT NULL, 
                max_teams NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_TEAM_PARAMETERS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_TEAM_PARAMETERS ADD CONSTRAINT CLARO_TEAM_PARAMETERS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_TEAM_PARAMETERS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_TEAM_PARAMETERS_AI_PK BEFORE INSERT ON CLARO_TEAM_PARAMETERS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_TEAM_PARAMETERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_TEAM_PARAMETERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_TEAM_PARAMETERS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_TEAM_PARAMETERS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C99EF54182D40A1F ON claro_team_parameters (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE5804D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580455D548E FOREIGN KEY (team_manager) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580459E625D1 FOREIGN KEY (team_manager_role) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_directory (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_team_parameters 
            ADD CONSTRAINT FK_C99EF54182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_team_users 
            DROP CONSTRAINT FK_B10C67F3296CD8AE
        ");
        $this->addSql("
            DROP TABLE claro_team
        ");
        $this->addSql("
            DROP TABLE claro_team_users
        ");
        $this->addSql("
            DROP TABLE claro_team_parameters
        ");
    }
}