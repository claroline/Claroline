<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/09/25 05:33:08
 */
class Version20130925173308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C54A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C5482D40A1F
        ');
        $this->addSql('
            DROP INDEX IDX_8D6D1C5482D40A1F ON claro_rssreader_configuration
        ');
        $this->addSql('
            DROP INDEX IDX_8D6D1C54A76ED395 ON claro_rssreader_configuration
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD widgetInstance_id INT DEFAULT NULL, 
            DROP user_id, 
            DROP workspace_id, 
            DROP is_default, 
            DROP is_desktop
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_8D6D1C54AB7B5A55 ON claro_rssreader_configuration (widgetInstance_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C54AB7B5A55
        ');
        $this->addSql('
            DROP INDEX IDX_8D6D1C54AB7B5A55 ON claro_rssreader_configuration
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD workspace_id INT DEFAULT NULL, 
            ADD is_default TINYINT(1) NOT NULL, 
            ADD is_desktop TINYINT(1) NOT NULL, 
            CHANGE widgetinstance_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C5482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_8D6D1C5482D40A1F ON claro_rssreader_configuration (workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_8D6D1C54A76ED395 ON claro_rssreader_configuration (user_id)
        ');
    }
}
