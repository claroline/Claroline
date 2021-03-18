<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/11 01:56:37
 */
class Version20210311135637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_log_message (
                id INT AUTO_INCREMENT NOT NULL, 
                receiver_id INT DEFAULT NULL, 
                sender_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                details LONGTEXT NOT NULL, 
                event VARCHAR(255) NOT NULL, 
                INDEX IDX_5AC39891C779CA5 (receiver_id), 
                INDEX IDX_5AC3989F624B39D (sender_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            ADD CONSTRAINT FK_5AC39891C779CA5 FOREIGN KEY (receiver_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            ADD CONSTRAINT FK_5AC3989F624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_log_message
        ');
    }
}
