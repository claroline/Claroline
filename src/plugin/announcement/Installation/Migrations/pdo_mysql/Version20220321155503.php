<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/21 03:55:05
 */
class Version20220321155503 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E361220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_announcement CHANGE creator_id creator_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E361220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_announcement CHANGE creator_id creator_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }
}
