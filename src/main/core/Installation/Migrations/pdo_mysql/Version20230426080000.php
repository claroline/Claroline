<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 08:37:49
 */
class Version20230426080000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if ($this->checkTableExists('claro_cursus_sessions', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursus_sessions DROP FOREIGN KEY FK_5256A81340AEF4B9');
        }

        if ($this->checkTableExists('claro_cursusbundle_courses_widget_config', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursusbundle_courses_widget_config DROP FOREIGN KEY FK_1724E27440AEF4B9');
        }

        if ($this->checkTableExists('claro_cursusbundle_cursus', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursusbundle_cursus DROP FOREIGN KEY FK_27921C33727ACA70');
        }

        if ($this->checkTableExists('claro_cursusbundle_cursus_group', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursusbundle_cursus_group DROP FOREIGN KEY FK_EA4DDE9340AEF4B9');
        }

        if ($this->checkTableExists('claro_cursusbundle_cursus_organizations', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursusbundle_cursus_organizations DROP FOREIGN KEY FK_3B65A4C840AEF4B9');
        }

        if ($this->checkTableExists('claro_cursusbundle_cursus_user', $this->connection)) {
            $this->addSql('ALTER TABLE claro_cursusbundle_cursus_user DROP FOREIGN KEY FK_8AA52D840AEF4B9');
        }

        if ($this->checkTableExists('claro_dropzonebundle_tool_document', $this->connection)) {
            $this->addSql('ALTER TABLE claro_dropzonebundle_tool_document DROP FOREIGN KEY FK_762E507A8F7B22CC');
        }

        if ($this->checkTableExists('claro_scorm_12_sco', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_12_sco DROP FOREIGN KEY FK_F900C289167AFF3D');
        }

        if ($this->checkTableExists('claro_scorm_12_sco', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_12_sco DROP FOREIGN KEY FK_F900C28948C689D5');
        }

        if ($this->checkTableExists('claro_scorm_12_sco_tracking', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_12_sco_tracking DROP FOREIGN KEY FK_465499F318A32826');
        }

        if ($this->checkTableExists('claro_scorm_2004_sco', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_2004_sco DROP FOREIGN KEY FK_E88F1DDD167AFF3D');
        }

        if ($this->checkTableExists('claro_scorm_2004_sco', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_2004_sco DROP FOREIGN KEY FK_E88F1DDD48C689D5');
        }

        if ($this->checkTableExists('claro_scorm_2004_sco_tracking', $this->connection)) {
            $this->addSql('ALTER TABLE claro_scorm_2004_sco_tracking DROP FOREIGN KEY FK_3A61C18A32826');
        }

        if ($this->checkTableExists('claro_workspace_required_resources', $this->connection)) {
            $this->addSql('ALTER TABLE claro_workspace_required_resources DROP FOREIGN KEY FK_85A0B2D9296B0ED5');
        }

        if ($this->checkTableExists('hevinci_ability_activity', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_ability_activity DROP FOREIGN KEY FK_46D92D328016D8B2');
            $this->addSql('ALTER TABLE hevinci_ability_activity DROP FOREIGN KEY FK_46D92D3281C06096');
        }

        if ($this->checkTableExists('hevinci_competency_activity', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_competency_activity DROP FOREIGN KEY FK_82CDDCBF81C06096');
            $this->addSql('ALTER TABLE hevinci_competency_activity DROP FOREIGN KEY FK_82CDDCBFFB9F58C');
        }

        if ($this->checkForeignKeyExists('FK_EDBF854473484933', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_objective_competency DROP FOREIGN KEY FK_EDBF854473484933');
        }

        if ($this->checkTableExists('hevinci_objective_group', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_objective_group DROP FOREIGN KEY FK_FFDC9E073484933');
        }

        if ($this->checkTableExists('hevinci_objective_progress', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_objective_progress DROP FOREIGN KEY FK_CAC2DC3873484933');
        }

        if ($this->checkTableExists('hevinci_objective_progress_log', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_objective_progress_log DROP FOREIGN KEY FK_F125F34773484933');
        }

        if ($this->checkTableExists('hevinci_objective_user', $this->connection)) {
            $this->addSql('ALTER TABLE hevinci_objective_user DROP FOREIGN KEY FK_6D032C1573484933');
        }

        if ($this->checkTableExists('icap__blog_post_tag', $this->connection)) {
            $this->addSql('ALTER TABLE icap__blog_post_tag DROP FOREIGN KEY FK_C3C6F479BAD26311');
        }

        $this->addSql('DROP TABLE IF EXISTS claro_cursus_sessions');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_course_registration_queue');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_course_session_validators');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_course_validators');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_courses_widget_config');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_cursus');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_cursus_displayed_word');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_cursus_group');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_cursus_organizations');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_cursus_user');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_document_model');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_session_event_comment');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_session_event_set');
        $this->addSql('DROP TABLE IF EXISTS claro_cursusbundle_session_event_tutors');
        $this->addSql('DROP TABLE IF EXISTS claro_database_backup');
        $this->addSql('DROP TABLE IF EXISTS claro_dropzonebundle_tool');
        $this->addSql('DROP TABLE IF EXISTS claro_dropzonebundle_tool_document');
        $this->addSql('DROP TABLE IF EXISTS claro_facet_role');
        $this->addSql('DROP TABLE IF EXISTS claro_general_facet_preference');
        $this->addSql('DROP TABLE IF EXISTS claro_panel_facet_role');
        $this->addSql('DROP TABLE IF EXISTS claro_role_options');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_12_resource');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_12_sco');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_12_sco_tracking');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_2004_resource');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_2004_sco');
        $this->addSql('DROP TABLE IF EXISTS claro_scorm_2004_sco_tracking');
        $this->addSql('DROP TABLE IF EXISTS claro_security_token');
        $this->addSql('DROP TABLE IF EXISTS claro_session');
        $this->addSql('DROP TABLE IF EXISTS claro_theme_color_collection');
        $this->addSql('DROP TABLE IF EXISTS claro_theme_poster_collection');
        $this->addSql('DROP TABLE IF EXISTS claro_user_administrator');
        $this->addSql('DROP TABLE IF EXISTS claro_user_options');
        $this->addSql('DROP TABLE IF EXISTS claro_video_track');
        $this->addSql('DROP TABLE IF EXISTS claro_widget_profile');
        $this->addSql('DROP TABLE IF EXISTS claro_workspace_required_resources');
        $this->addSql('DROP TABLE IF EXISTS claro_workspace_requirements');
        $this->addSql('DROP TABLE IF EXISTS hevinci_ability_progress');
        $this->addSql('DROP TABLE IF EXISTS hevinci_competency_progress');
        $this->addSql('DROP TABLE IF EXISTS hevinci_competency_progress_log');
        $this->addSql('DROP TABLE IF EXISTS hevinci_learning_objective');
        $this->addSql('DROP TABLE IF EXISTS hevinci_objective_competency');
        $this->addSql('DROP TABLE IF EXISTS hevinci_objective_group');
        $this->addSql('DROP TABLE IF EXISTS hevinci_objective_progress');
        $this->addSql('DROP TABLE IF EXISTS hevinci_objective_progress_log');
        $this->addSql('DROP TABLE IF EXISTS hevinci_objective_user');
        $this->addSql('DROP TABLE IF EXISTS hevinci_user_progress');
        $this->addSql('DROP TABLE IF EXISTS hevinci_user_progress_log');
        $this->addSql('DROP TABLE IF EXISTS hevinci_ability_activity');
        $this->addSql('DROP TABLE IF EXISTS hevinci_competency_activity');
        $this->addSql('DROP TABLE IF EXISTS icap__blog_post_tag');
        $this->addSql('DROP TABLE IF EXISTS icap__blog_tag');

        $this->addSql('
            ALTER TABLE claro_planned_object CHANGE event_class event_class VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
