<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/10/21 09:37:50
 */
class Version20151021093748 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_path_category_path (
                path_id INT NOT NULL, 
                category_id INT NOT NULL, 
                INDEX IDX_69C87C68D96C566B (path_id), 
                INDEX IDX_69C87C6812469DE2 (category_id), 
                PRIMARY KEY(path_id, category_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE innova_path_category_path 
            ADD CONSTRAINT FK_69C87C68D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_path_category_path 
            ADD CONSTRAINT FK_69C87C6812469DE2 FOREIGN KEY (category_id) 
            REFERENCES innova_path_category (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_path_category_path
        ");
    }
}