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
 * Generation date: 2013/08/05 11:00:05
 */
class Version20130805110005 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_rssreader_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                url VARCHAR(255) NOT NULL, 
                is_default TINYINT(1) NOT NULL, 
                is_desktop TINYINT(1) NOT NULL, 
                INDEX IDX_8D6D1C5482D40A1F (workspace_id), 
                INDEX IDX_8D6D1C54A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C5482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ');
        $this->addSql('
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_rssreader_configuration
        ');
    }
}
