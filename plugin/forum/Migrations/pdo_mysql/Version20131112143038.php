<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/12 02:30:40
 */
class Version20131112143038 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_forum_notification (
                id INT AUTO_INCREMENT NOT NULL, 
                forum_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                INDEX IDX_1330B0B629CCBAD0 (forum_id), 
                INDEX IDX_1330B0B6A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_forum_notification 
            ADD CONSTRAINT FK_1330B0B629CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_notification 
            ADD CONSTRAINT FK_1330B0B6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_forum_notification
        ');
    }
}
