<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

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
        if ($this->checkTableExists('claro_activity_parameters', $this->connection)) {
            $this->addSql('ALTER TABLE claro_activity_parameters DROP FOREIGN KEY FK_E2EE25E281C06096');
        }

        if ($this->checkTableExists('claro_activity', $this->connection)) {
            $this->addSql('ALTER TABLE claro_activity DROP FOREIGN KEY FK_E4A67CAC88BD9C1F');
        }

        if ($this->checkTableExists('claro_activity_evaluation', $this->connection)) {
            $this->addSql('ALTER TABLE claro_activity_evaluation DROP FOREIGN KEY FK_F75EC869896F55DB');
        }

        if ($this->checkTableExists('claro_activity_past_evaluation', $this->connection)) {
            $this->addSql('ALTER TABLE claro_activity_past_evaluation DROP FOREIGN KEY FK_F1A76182896F55DB');
        }

        if ($this->checkTableExists('claro_activity_secondary_resources', $this->connection)) {
            $this->addSql('ALTER TABLE claro_activity_secondary_resources DROP FOREIGN KEY FK_713242A7DB5E3CF7');
        }

        $this->addSql('DROP TABLE IF EXISTS claro_activity');
        $this->addSql('DROP TABLE IF EXISTS claro_activity_evaluation');
        $this->addSql('DROP TABLE IF EXISTS claro_activity_parameters');
        $this->addSql('DROP TABLE IF EXISTS claro_activity_past_evaluation');
        $this->addSql('DROP TABLE IF EXISTS claro_activity_secondary_resources');
    }

    public function down(Schema $schema): void
    {
    }
}
