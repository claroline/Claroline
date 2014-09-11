<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                numberLastAwardedBadge INT NOT NULL, 
                numberMostAwardedBadge INT NOT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_9A2EA78BAB7B5A55 (widgetInstance_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_badge_usage_config 
            ADD CONSTRAINT FK_9A2EA78BAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD authentication VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_badge_usage_config
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP authentication
        ");
    }
}