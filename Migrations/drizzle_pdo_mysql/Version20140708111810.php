<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                stringValue VARCHAR(255) DEFAULT NULL, 
                floatValue DOUBLE PRECISION DEFAULT NULL, 
                dateValue DATETIME DEFAULT NULL, 
                fieldFacet_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_35307C0AA76ED395 (user_id), 
                INDEX IDX_35307C0A9F9239AF (fieldFacet_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_event_event_category (
                event_id INT NOT NULL, 
                eventcategory_id INT NOT NULL, 
                PRIMARY KEY(event_id, eventcategory_id), 
                INDEX IDX_858F0D4C71F7E88B (event_id), 
                INDEX IDX_858F0D4C29E3B4B5 (eventcategory_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                `position` INT NOT NULL, 
                isVisibleByOwner BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 (name)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_facet_role (
                facet_id INT NOT NULL, 
                role_id INT NOT NULL, 
                PRIMARY KEY(facet_id, role_id), 
                INDEX IDX_CDD5845DFC889F24 (facet_id), 
                INDEX IDX_CDD5845DD60322AC (role_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_role (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                canOpen BOOLEAN NOT NULL, 
                canEdit BOOLEAN NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_12F52A52D60322AC (role_id), 
                INDEX IDX_12F52A529F9239AF (fieldFacet_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                baseData BOOLEAN NOT NULL, 
                mail BOOLEAN NOT NULL, 
                phone BOOLEAN NOT NULL, 
                sendMail BOOLEAN NOT NULL, 
                sendMessage BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_38AACF88D60322AC (role_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                facet_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                `position` INT NOT NULL, 
                isVisibleByOwner BOOLEAN NOT NULL, 
                isEditableByOwner BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F6C21DB2FC889F24 (facet_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                activity_parameters_id INT DEFAULT NULL, 
                log_id INT DEFAULT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details TEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                PRIMARY KEY(id), 
                INDEX IDX_F1A76182A76ED395 (user_id), 
                INDEX IDX_F1A76182896F55DB (activity_parameters_id), 
                INDEX IDX_F1A76182EA675D86 (log_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_id INT DEFAULT NULL, 
                max_duration INT DEFAULT NULL, 
                max_attempts INT DEFAULT NULL, 
                who VARCHAR(255) DEFAULT NULL, 
                activity_where VARCHAR(255) DEFAULT NULL, 
                with_tutor BOOLEAN DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_E2EE25E281C06096 (activity_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activityparameters_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                PRIMARY KEY(
                    activityparameters_id, resourcenode_id
                ), 
                INDEX IDX_713242A7DB5E3CF7 (activityparameters_id), 
                INDEX IDX_713242A777C292AE (resourcenode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                log_id INT DEFAULT NULL, 
                lastest_evaluation_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details TEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                total_duration INT DEFAULT NULL, 
                attempts_count INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F75EC869A76ED395 (user_id), 
                INDEX IDX_F75EC869896F55DB (activity_parameters_id), 
                INDEX IDX_F75EC869EA675D86 (log_id), 
                UNIQUE INDEX user_activity_unique_evaluation (user_id, activity_parameters_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                log_action VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_C8835D2098EC6B7B (resource_type_id), 
                UNIQUE INDEX activity_rule_unique_action_resource_type (log_action, resource_type_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                badge_id INT DEFAULT NULL, 
                result_visible BOOLEAN DEFAULT NULL, 
                occurrence INT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                `result` VARCHAR(255) DEFAULT NULL, 
                resultMax VARCHAR(255) DEFAULT NULL, 
                resultComparison INT DEFAULT NULL, 
                userType INT NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_6824A65E896F55DB (activity_parameters_id), 
                INDEX IDX_6824A65E89329D25 (resource_id), 
                INDEX IDX_6824A65EF7A2C2FC (badge_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_event_category (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_408DC8C05E237E06 (name)
            )
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
            ADD accessible_from DATETIME DEFAULT NULL, 
            ADD accessible_until DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70 ON claro_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP parent_id, 
            DROP discr, 
            DROP lft, 
            DROP lvl, 
            DROP rgt, 
            DROP root
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD workspace_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD resultMax VARCHAR(255) DEFAULT NULL, 
            ADD active_from DATETIME DEFAULT NULL, 
            ADD active_until DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD parameters_id INT DEFAULT NULL, 
            ADD title VARCHAR(255) DEFAULT NULL, 
            ADD primaryResource_id INT DEFAULT NULL, 
            DROP start_date, 
            DROP end_date, 
            CHANGE instruction description VARCHAR(255) NOT NULL
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
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DFC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            DROP FOREIGN KEY FK_12F52A529F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A7DB5E3CF7
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP FOREIGN KEY FK_6824A65E896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            DROP FOREIGN KEY FK_858F0D4C29E3B4B5
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
            DROP INDEX IDX_E4A67CAC52410EEC ON claro_activity
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD start_date DATETIME DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL, 
            DROP parameters_id, 
            DROP title, 
            DROP primaryResource_id, 
            CHANGE description instruction VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP resultMax, 
            DROP active_from, 
            DROP active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP accessible_from, 
            DROP accessible_until
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD parent_id INT DEFAULT NULL, 
            ADD discr VARCHAR(255) NOT NULL, 
            ADD lft INT DEFAULT NULL, 
            ADD lvl INT DEFAULT NULL, 
            ADD rgt INT DEFAULT NULL, 
            ADD root INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP workspace_id
        ");
    }
}