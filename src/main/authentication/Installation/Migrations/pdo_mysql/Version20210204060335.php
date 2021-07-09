<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/02/04 06:03:36
 */
class Version20210204060335 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_ip_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ip VARCHAR(255) NOT NULL, 
                is_range TINYINT(1) NOT NULL,
                is_locked TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_FEB73761A5E3B32D (ip), 
                INDEX IDX_FEB73761A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user 
            ADD CONSTRAINT FK_FEB73761A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_ip_user
        ');
    }
}
