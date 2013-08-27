<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 11:29:47
 */
class Version20130827112946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_home_tab_main_config (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                allow_desktop_tab_creation NUMBER(1) NOT NULL, 
                allow_workspace_tab_creation NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_HOME_TAB_MAIN_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_HOME_TAB_MAIN_CONFIG ADD CONSTRAINT CLARO_HOME_TAB_MAIN_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_HOME_TAB_MAIN_CONFIG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_HOME_TAB_MAIN_CONFIG_AI_PK BEFORE INSERT ON CLARO_HOME_TAB_MAIN_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_HOME_TAB_MAIN_CONFIG_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_HOME_TAB_MAIN_CONFIG_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_HOME_TAB_MAIN_CONFIG_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_HOME_TAB_MAIN_CONFIG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C749B4E7A76ED395 ON claro_home_tab_main_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C749B4E782D40A1F ON claro_home_tab_main_config (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_main_config_unique_user_workspace ON claro_home_tab_main_config (user_id, workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id NUMBER(10) NOT NULL, 
                home_tab_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                is_visible NUMBER(1) NOT NULL, 
                is_locked NUMBER(1) NOT NULL, 
                tab_order NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_HOME_TAB_CONFIG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_HOME_TAB_CONFIG ADD CONSTRAINT CLARO_HOME_TAB_CONFIG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_HOME_TAB_CONFIG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_HOME_TAB_CONFIG_AI_PK BEFORE INSERT ON CLARO_HOME_TAB_CONFIG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_HOME_TAB_CONFIG_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; IF (
                :NEW.ID IS NULL 
                OR :NEW.ID = 0
            ) THEN 
            SELECT CLARO_HOME_TAB_CONFIG_ID_SEQ.NEXTVAL INTO :NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_HOME_TAB_CONFIG_ID_SEQ'; 
            SELECT :NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_HOME_TAB_CONFIG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE7D08FA9E ON claro_home_tab_config (home_tab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BEA76ED395 ON claro_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE82D40A1F ON claro_home_tab_config (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config (
                home_tab_id, user_id, workspace_id
            )
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_main_config 
            ADD CONSTRAINT FK_C749B4E7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_main_config 
            ADD CONSTRAINT FK_C749B4E782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_user_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user ON claro_home_tab (name, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_workspace ON claro_home_tab (name, workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_home_tab_main_config
        ");
        $this->addSql("
            DROP TABLE claro_home_tab_config
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_user
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user_workspace ON claro_home_tab (name, user_id, workspace_id)
        ");
    }
}