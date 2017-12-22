<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/09/08 04:36:38
 */
class Version20170925163637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_workspace_recent (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                entry_date DATETIME NOT NULL, 
                INDEX IDX_ED82CA3B82D40A1F (workspace_id), 
                INDEX user_idx (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            ADD CONSTRAINT FK_ED82CA3BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            ADD CONSTRAINT FK_ED82CA3B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_workspace_recent
        ');
    }
}
