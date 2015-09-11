<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/15 12:04:11
 */
class Version20150715120410 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_sequence_step (
                id INT AUTO_INCREMENT NOT NULL, 
                sequence_id INT NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                position SMALLINT DEFAULT NULL, 
                shuffle TINYINT(1) NOT NULL, 
                is_first TINYINT(1) NOT NULL, 
                is_last TINYINT(1) NOT NULL, 
                INDEX IDX_2AE7A31998FB19AE (sequence_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE ujm_sequence (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CB11F712B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE ujm_sequence_step 
            ADD CONSTRAINT FK_2AE7A31998FB19AE FOREIGN KEY (sequence_id) 
            REFERENCES ujm_sequence (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_sequence 
            ADD CONSTRAINT FK_CB11F712B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_sequence_step 
            DROP FOREIGN KEY FK_2AE7A31998FB19AE
        ");
        $this->addSql("
            DROP TABLE ujm_sequence_step
        ");
        $this->addSql("
            DROP TABLE ujm_sequence
        ");
    }
}