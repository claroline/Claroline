<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/10 08:43:10
 */
class Version20210310084309 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_task (
                id INT AUTO_INCREMENT NOT NULL, 
                done TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                event_id INT NOT NULL,
                workspace_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_3460253ED17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253E71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3460253E71F7E88B ON claro_task (event_id)
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3460253E82D40A1F ON claro_task (workspace_id)
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB5A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB5A76ED395 ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            CHANGE user_id creator_id INT DEFAULT NULL, 
            CHANGE title entity_name VARCHAR(255) NOT NULL,
            DROP is_all_day
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB561220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB561220EA6 ON claro_event (creator_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_task
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB561220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB561220EA6 ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            CHANGE creator_id user_id INT NOT NULL, 
            CHANGE entity_name title VARCHAR(50) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
            ADD is_all_day TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB5A76ED395 ON claro_event (user_id)
        ');
    }
}
