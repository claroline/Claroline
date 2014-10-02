<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 11:04:58
 */
class Version20140911110455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_badge_usage_config (
                id INT IDENTITY NOT NULL, 
                numberLastAwardedBadge SMALLINT NOT NULL, 
                numberMostAwardedBadge SMALLINT NOT NULL, 
                widgetInstance_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9A2EA78BAB7B5A55 ON claro_widget_badge_usage_config (widgetInstance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_badge_usage_config 
            ADD CONSTRAINT FK_9A2EA78BAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD authentication NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_badge_usage_config
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN authentication
        ");
    }
}