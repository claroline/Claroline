<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/29 04:38:59
 */
class Version20150729163857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_friend_request (
                id INT AUTO_INCREMENT NOT NULL, 
                host VARCHAR(255) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_activated TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_api_claroline_access (
                id INT AUTO_INCREMENT NOT NULL, 
                friend_request_id INT DEFAULT NULL, 
                random_id VARCHAR(255) NOT NULL, 
                secret VARCHAR(255) NOT NULL, 
                access_token VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2B10E8B1EC394CA1 (friend_request_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_pending_friend (
                id INT AUTO_INCREMENT NOT NULL, 
                ip VARCHAR(255) NOT NULL, 
                host VARCHAR(255) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_api_claroline_access 
            ADD CONSTRAINT FK_2B10E8B1EC394CA1 FOREIGN KEY (friend_request_id) 
            REFERENCES claro_friend_request (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_type 
            ADD template VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_api_claroline_access 
            DROP FOREIGN KEY FK_2B10E8B1EC394CA1
        ');
        $this->addSql('
            DROP TABLE claro_friend_request
        ');
        $this->addSql('
            DROP TABLE claro_api_claroline_access
        ');
        $this->addSql('
            DROP TABLE claro_pending_friend
        ');
        $this->addSql('
            ALTER TABLE claro_type 
            DROP template
        ');
    }
}
