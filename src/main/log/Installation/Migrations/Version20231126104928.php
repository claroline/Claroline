<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/26 10:49:43
 */
final class Version20231126104928 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_log_operational (
                id INT AUTO_INCREMENT NOT NULL, 
                doer_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                event VARCHAR(255) NOT NULL, 
                details LONGTEXT NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                doer_country VARCHAR(255) DEFAULT NULL, 
                doer_city VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_3BB0DC0E12D3860F (doer_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_log_operational 
            ADD CONSTRAINT FK_3BB0DC0E12D3860F FOREIGN KEY (doer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_operational 
            DROP FOREIGN KEY FK_3BB0DC0E12D3860F
        ');
        $this->addSql('
            DROP TABLE claro_log_operational
        ');
    }
}
