<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/10/04 10:46:01
 */
class Version20171004104559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD task_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E38DB60186 FOREIGN KEY (task_id) 
            REFERENCES claro_scheduled_task (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_778754E38DB60186 ON claro_announcement (task_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E38DB60186
        ');
        $this->addSql('
            DROP INDEX IDX_778754E38DB60186 ON claro_announcement
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP task_id
        ');
    }
}
