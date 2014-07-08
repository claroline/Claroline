<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/08 11:18:15
 */
class Version20140708111810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                stringValue NVARCHAR(255), 
                floatValue DOUBLE PRECISION, 
                dateValue DATETIME2(6), 
                fieldFacet_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0AA76ED395 ON claro_field_facet_value (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0A9F9239AF ON claro_field_facet_value (fieldFacet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event_event_category (
                event_id INT NOT NULL, 
                eventcategory_id INT NOT NULL, 
                PRIMARY KEY (event_id, eventcategory_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C71F7E88B ON claro_event_event_category (event_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C29E3B4B5 ON claro_event_event_category (eventcategory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_facet (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                isVisibleByOwner BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_facet_role (
                facet_id INT NOT NULL, 
                role_id INT NOT NULL, 
                PRIMARY KEY (facet_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DFC889F24 ON claro_facet_role (facet_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DD60322AC ON claro_facet_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_role (
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                canOpen BIT NOT NULL, 
                canEdit BIT NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A52D60322AC ON claro_field_facet_role (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A529F9239AF ON claro_field_facet_role (fieldFacet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                baseData BIT NOT NULL, 
                mail BIT NOT NULL, 
                phone BIT NOT NULL, 
                sendMail BIT NOT NULL, 
                sendMessage BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INT IDENTITY NOT NULL, 
                facet_id INT NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                position INT NOT NULL, 
                isVisibleByOwner BIT NOT NULL, 
                isEditableByOwner BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                activity_parameters_id INT, 
                log_id INT, 
                evaluation_date DATETIME2(6), 
                evaluation_type NVARCHAR(255), 
                evaluation_status NVARCHAR(255), 
                duration INT, 
                score NVARCHAR(255), 
                score_num INT, 
                score_min INT, 
                score_max INT, 
                evaluation_comment NVARCHAR(255), 
                details VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id INT IDENTITY NOT NULL, 
                activity_id INT, 
                max_duration INT, 
                max_attempts INT, 
                who NVARCHAR(255), 
                activity_where NVARCHAR(255), 
                with_tutor BIT, 
                evaluation_type NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id) 
            WHERE activity_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activityparameters_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                PRIMARY KEY (
                    activityparameters_id, resourcenode_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7DB5E3CF7 ON claro_activity_secondary_resources (activityparameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A777C292AE ON claro_activity_secondary_resources (resourcenode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                log_id INT, 
                lastest_evaluation_date DATETIME2(6), 
                evaluation_type NVARCHAR(255), 
                evaluation_status NVARCHAR(255), 
                duration INT, 
                score NVARCHAR(255), 
                score_num INT, 
                score_min INT, 
                score_max INT, 
                evaluation_comment NVARCHAR(255), 
                details VARCHAR(MAX), 
                total_duration INT, 
                attempts_count INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id) 
            WHERE user_id IS NOT NULL 
            AND activity_parameters_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id INT IDENTITY NOT NULL, 
                resource_type_id INT, 
                log_action NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8835D2098EC6B7B ON claro_activity_rule_action (resource_type_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX activity_rule_unique_action_resource_type ON claro_activity_rule_action (log_action, resource_type_id) 
            WHERE log_action IS NOT NULL 
            AND resource_type_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id INT IDENTITY NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                resource_id INT, 
                badge_id INT, 
                result_visible BIT, 
                occurrence SMALLINT NOT NULL, 
                action NVARCHAR(255) NOT NULL, 
                result NVARCHAR(255), 
                resultMax NVARCHAR(255), 
                resultComparison SMALLINT, 
                userType SMALLINT NOT NULL, 
                active_from DATETIME2(6), 
                active_until DATETIME2(6), 
                PRIMARY KEY (id)
            )
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
            CREATE TABLE claro_event_category (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_408DC8C05E237E06 ON claro_event_category (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0A9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C29E3B4B5 FOREIGN KEY (eventcategory_id) 
            REFERENCES claro_event_category (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A52D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A529F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7DB5E3CF7 FOREIGN KEY (activityparameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A777C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
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
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_until DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN discr
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN lft
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN lvl
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN rgt
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN root
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT FK_D9028545727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D9028545727ACA70'
            ) 
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT IDX_D9028545727ACA70 ELSE 
            DROP INDEX IDX_D9028545727ACA70 ON claro_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD workspace_id INT
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
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD resultMax NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_until DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            sp_RENAME 'claro_activity.instruction', 
            'description', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD parameters_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD title NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD primaryResource_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN start_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN end_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN description NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC52410EEC ON claro_activity (primaryResource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id) 
            WHERE parameters_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet_role 
            DROP CONSTRAINT FK_CDD5845DFC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            DROP CONSTRAINT FK_35307C0A9F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            DROP CONSTRAINT FK_12F52A529F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP CONSTRAINT FK_F1A76182896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A7DB5E3CF7
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT FK_F75EC869896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP CONSTRAINT FK_6824A65E896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            DROP CONSTRAINT FK_858F0D4C29E3B4B5
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_value
        ");
        $this->addSql("
            DROP TABLE claro_event_event_category
        ");
        $this->addSql("
            DROP TABLE claro_facet
        ");
        $this->addSql("
            DROP TABLE claro_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_event_category
        ");
        $this->addSql("
            sp_RENAME 'claro_activity.description', 
            'instruction', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD start_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD end_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN parameters_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN title
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN primaryResource_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN instruction NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CAC52410EEC'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CAC52410EEC ELSE 
            DROP INDEX IDX_E4A67CAC52410EEC ON claro_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_E4A67CAC88BD9C1F'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT UNIQ_E4A67CAC88BD9C1F ELSE 
            DROP INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN resultMax
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN active_from
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_from
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_until
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD discr NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD lft INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD lvl INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD rgt INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD root INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP COLUMN workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_C8EFD7EF82D40A1F'
            ) 
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT IDX_C8EFD7EF82D40A1F ELSE 
            DROP INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag
        ");
    }
}