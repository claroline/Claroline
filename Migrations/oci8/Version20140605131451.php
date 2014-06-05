<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/05 01:14:53
 */
class Version20140605131451 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id NUMBER(10) NOT NULL, 
                resource_type_id NUMBER(10) DEFAULT NULL, 
                log_action VARCHAR2(255) NOT NULL, 
                rule_type VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ACTIVITY_RULE_ACTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ACTIVITY_RULE_ACTION ADD CONSTRAINT CLARO_ACTIVITY_RULE_ACTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ACTIVITY_RULE_ACTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ACTIVITY_RULE_ACTION_AI_PK BEFORE INSERT ON CLARO_ACTIVITY_RULE_ACTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ACTIVITY_RULE_ACTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ACTIVITY_RULE_ACTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ACTIVITY_RULE_ACTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ACTIVITY_RULE_ACTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C8835D2098EC6B7B ON claro_activity_rule_action (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id NUMBER(10) NOT NULL, 
                activity_parameters_id NUMBER(10) NOT NULL, 
                resource_id NUMBER(10) DEFAULT NULL, 
                badge_id NUMBER(10) DEFAULT NULL, 
                occurrence NUMBER(5) NOT NULL, 
                action VARCHAR2(255) NOT NULL, 
                result VARCHAR2(255) DEFAULT NULL, 
                resultComparison NUMBER(5) DEFAULT NULL, 
                userType NUMBER(5) NOT NULL, 
                additional_datas VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ACTIVITY_RULE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ACTIVITY_RULE ADD CONSTRAINT CLARO_ACTIVITY_RULE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ACTIVITY_RULE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ACTIVITY_RULE_AI_PK BEFORE INSERT ON CLARO_ACTIVITY_RULE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ACTIVITY_RULE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ACTIVITY_RULE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ACTIVITY_RULE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ACTIVITY_RULE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E896F55DB ON claro_activity_rule (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E89329D25 ON claro_activity_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule (badge_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                activity_parameters_id NUMBER(10) NOT NULL, 
                last_date TIMESTAMP(0) DEFAULT NULL, 
                evaluation_type VARCHAR2(255) DEFAULT NULL, 
                evaluation_status VARCHAR2(255) DEFAULT NULL, 
                duration NUMBER(10) DEFAULT NULL, 
                score VARCHAR2(255) DEFAULT NULL, 
                score_num NUMBER(10) DEFAULT NULL, 
                score_min NUMBER(10) DEFAULT NULL, 
                score_max NUMBER(10) DEFAULT NULL, 
                evaluation_comment VARCHAR2(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                total_duration NUMBER(10) DEFAULT NULL, 
                attempts_count NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ACTIVITY_EVALUATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ACTIVITY_EVALUATION ADD CONSTRAINT CLARO_ACTIVITY_EVALUATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ACTIVITY_EVALUATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ACTIVITY_EVALUATION_AI_PK BEFORE INSERT ON CLARO_ACTIVITY_EVALUATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ACTIVITY_EVALUATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ACTIVITY_EVALUATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ACTIVITY_EVALUATION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ACTIVITY_EVALUATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_activity_evaluation.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id NUMBER(10) NOT NULL, 
                activity_id NUMBER(10) DEFAULT NULL, 
                max_duration NUMBER(10) DEFAULT NULL, 
                max_attempts NUMBER(10) DEFAULT NULL, 
                evaluation_type VARCHAR2(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ACTIVITY_PARAMETERS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ACTIVITY_PARAMETERS ADD CONSTRAINT CLARO_ACTIVITY_PARAMETERS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ACTIVITY_PARAMETERS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ACTIVITY_PARAMETERS_AI_PK BEFORE INSERT ON CLARO_ACTIVITY_PARAMETERS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ACTIVITY_PARAMETERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ACTIVITY_PARAMETERS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ACTIVITY_PARAMETERS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ACTIVITY_PARAMETERS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activity_parameters_id NUMBER(10) NOT NULL, 
                resource_node_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(
                    activity_parameters_id, resource_node_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7896F55DB ON claro_activity_secondary_resources (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A71BAD783F ON claro_activity_secondary_resources (resource_node_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                activity_parameters_id NUMBER(10) DEFAULT NULL, 
                last_date TIMESTAMP(0) DEFAULT NULL, 
                evaluation_type VARCHAR2(255) DEFAULT NULL, 
                evaluation_status VARCHAR2(255) DEFAULT NULL, 
                duration NUMBER(10) DEFAULT NULL, 
                score VARCHAR2(255) DEFAULT NULL, 
                score_num NUMBER(10) DEFAULT NULL, 
                score_min NUMBER(10) DEFAULT NULL, 
                score_max NUMBER(10) DEFAULT NULL, 
                evaluation_comment VARCHAR2(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ACTIVITY_PAST_EVALUATION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ACTIVITY_PAST_EVALUATION ADD CONSTRAINT CLARO_ACTIVITY_PAST_EVALUATION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ACTIVITY_PAST_EVALUATION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ACTIVITY_PAST_EVALUATION_AI_PK BEFORE INSERT ON CLARO_ACTIVITY_PAST_EVALUATION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ACTIVITY_PAST_EVALUATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ACTIVITY_PAST_EVALUATION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ACTIVITY_PAST_EVALUATION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ACTIVITY_PAST_EVALUATION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_activity_past_evaluation.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule_action 
            ADD CONSTRAINT FK_C8835D2098EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A71BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD (
                accessible_from TIMESTAMP(0) DEFAULT NULL, 
                accessible_until TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD (
                parameters_id NUMBER(10) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity MODIFY (
                resourceNode_id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity RENAME COLUMN instruction TO description
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP (start_date, end_date)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD (
                additional_datas VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD (
                workspace_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP CONSTRAINT FK_6824A65E896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT FK_F75EC869896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A7896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP CONSTRAINT FK_F1A76182896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD (
                start_date TIMESTAMP(0) DEFAULT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity MODIFY (
                resourceNode_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity RENAME COLUMN description TO instruction
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP (parameters_id, title)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP (additional_datas)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP (
                accessible_from, accessible_until
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F
        ");
    }
}