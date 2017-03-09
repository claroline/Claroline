<?php

namespace Claroline\RemoteUserSynchronizationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/09 09:56:31
 */
class Version20170309095629 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_remote_user_token (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                expiration_date DATETIME NOT NULL, 
                activated TINYINT(1) NOT NULL, 
                INDEX IDX_FF885ADCA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_remote_user_token 
            ADD CONSTRAINT FK_FF885ADCA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_remote_user_token
        ');
    }
}
