<?php

namespace Claroline\ResultBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/21 10:02:46
 */
class Version20160321100245 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_result (
                id INT AUTO_INCREMENT NOT NULL, 
                date DATETIME DEFAULT NULL, 
                total INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E059B38CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_result_mark (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                result_id INT DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                INDEX IDX_7D93D85EA76ED395 (user_id), 
                INDEX IDX_7D93D85E7A7B643 (result_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_result 
            ADD CONSTRAINT FK_E059B38CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_result_mark 
            ADD CONSTRAINT FK_7D93D85EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_result_mark 
            ADD CONSTRAINT FK_7D93D85E7A7B643 FOREIGN KEY (result_id) 
            REFERENCES claro_result (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_result_mark 
            DROP FOREIGN KEY FK_7D93D85E7A7B643
        ');
        $this->addSql('
            DROP TABLE claro_result
        ');
        $this->addSql('
            DROP TABLE claro_result_mark
        ');
    }
}
