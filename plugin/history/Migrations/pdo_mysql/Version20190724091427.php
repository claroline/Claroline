<?php

namespace Claroline\HistoryBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/07/24 09:14:29
 */
class Version20190724091427 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_resource_recent (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                resource_id INT NOT NULL, 
                createdAt DATETIME NOT NULL, 
                INDEX IDX_544B72FE89329D25 (resource_id), 
                INDEX user_idx (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            ADD CONSTRAINT FK_544B72FEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            ADD CONSTRAINT FK_544B72FE89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent CHANGE entry_date createdAt DATETIME NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_resource_recent
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent CHANGE createdat entry_date DATETIME NOT NULL
        ');
    }
}
