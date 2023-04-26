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
class Version20230426120000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if ($this->checkTableExists('claro_bookingbundle_material_booking', $this->connection)) {
            $this->addSql('ALTER TABLE claro_bookingbundle_material_booking DROP FOREIGN KEY FK_7D5ED1AAE308AC6F');
        }
        if ($this->checkTableExists('claro_bookingbundle_room_booking', $this->connection)) {
            $this->addSql('ALTER TABLE claro_bookingbundle_room_booking DROP FOREIGN KEY FK_943D1D8554177093');
        }
        if ($this->checkTableExists('claro_plannednotificationbundle_planned_notification', $this->connection)) {
            $this->addSql('ALTER TABLE claro_plannednotificationbundle_planned_notification DROP FOREIGN KEY FK_DB5DE453537A1329');
        }
        if ($this->checkTableExists('claro_plannednotificationbundle_planned_notification_role', $this->connection)) {
            $this->addSql('ALTER TABLE claro_plannednotificationbundle_planned_notification_role DROP FOREIGN KEY FK_2A4A3832EAB5688');
        }
        if ($this->checkTableExists('icap__socialmedia_wall_item', $this->connection)) {
            $this->addSql('ALTER TABLE icap__socialmedia_wall_item DROP FOREIGN KEY FK_436BC420F8697D13');
        }
        if ($this->checkTableExists('icap__socialmedia_wall_item', $this->connection)) {
            $this->addSql('ALTER TABLE icap__socialmedia_wall_item DROP FOREIGN KEY FK_436BC420859BFA32');
        }
        if ($this->checkTableExists('icap__socialmedia_wall_item', $this->connection)) {
            $this->addSql('ALTER TABLE icap__socialmedia_wall_item DROP FOREIGN KEY FK_436BC4202AE63FDB');
        }
        if ($this->checkTableExists('ujm_lti_resource', $this->connection)) {
            $this->addSql('ALTER TABLE ujm_lti_resource DROP FOREIGN KEY FK_43618A03A58375FA');
        }
        if ($this->checkTableExists('ujm_ltiapp_workspace', $this->connection)) {
            $this->addSql('ALTER TABLE ujm_ltiapp_workspace DROP FOREIGN KEY FK_7FB6D142A22F70CC');
        }

        $this->addSql('DROP TABLE IF EXISTS claro_bookingbundle_material');
        $this->addSql('DROP TABLE IF EXISTS claro_bookingbundle_material_booking');
        $this->addSql('DROP TABLE IF EXISTS claro_bookingbundle_room');
        $this->addSql('DROP TABLE IF EXISTS claro_bookingbundle_room_booking');
        $this->addSql('DROP TABLE IF EXISTS claro_plannednotificationbundle_message');
        $this->addSql('DROP TABLE IF EXISTS claro_plannednotificationbundle_planned_notification');
        $this->addSql('DROP TABLE IF EXISTS claro_plannednotificationbundle_planned_notification_role');
        $this->addSql('DROP TABLE IF EXISTS claro_team_parameters');
        $this->addSql('DROP TABLE IF EXISTS claro_widget_progression');
        $this->addSql('DROP TABLE IF EXISTS icap__socialmedia_comment');
        $this->addSql('DROP TABLE IF EXISTS icap__socialmedia_like');
        $this->addSql('DROP TABLE IF EXISTS icap__socialmedia_note');
        $this->addSql('DROP TABLE IF EXISTS icap__socialmedia_share');
        $this->addSql('DROP TABLE IF EXISTS icap__socialmedia_wall_item');
        $this->addSql('DROP TABLE IF EXISTS ujm_lti_app');
        $this->addSql('DROP TABLE IF EXISTS ujm_lti_resource');
        $this->addSql('DROP TABLE IF EXISTS ujm_ltiapp_workspace');
    }

    public function down(Schema $schema): void
    {
    }
}
