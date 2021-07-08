<?php

namespace Claroline\AnalyticsBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/02 06:53:03
 */
class Version20200702065301 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if (!$this->checkTableExists('claro_widget_progression', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_widget_progression (
                    id INT AUTO_INCREMENT NOT NULL, 
                    level_max INT DEFAULT NULL, 
                    widgetInstance_id INT NOT NULL, 
                    INDEX IDX_7F12EAC4AB7B5A55 (widgetInstance_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
            ');

            $this->addSql('
                ALTER TABLE claro_widget_progression 
                ADD CONSTRAINT FK_7F12EAC4AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_widget_progression
        ');
    }
}
