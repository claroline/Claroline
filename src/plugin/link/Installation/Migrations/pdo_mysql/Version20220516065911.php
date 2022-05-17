<?php

namespace Claroline\LinkBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/05/16 06:59:15
 */
class Version20220516065911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab_tool_shortcut (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                tool VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_ED7350108D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_tool_shortcut 
            ADD CONSTRAINT FK_ED7350108D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_home_tab_tool_shortcut
        ');
    }
}
