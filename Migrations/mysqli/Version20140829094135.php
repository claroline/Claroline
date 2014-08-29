<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/29 09:41:38
 */
class Version20140829094135 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                user_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_F461C538D60322AC (role_id), 
                INDEX IDX_F461C538A76ED395 (user_id), 
                INDEX IDX_F461C53882D40A1F (workspace_id), 
                UNIQUE INDEX user_role_unique (role_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C538D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C538A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C53882D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_registration_queue
        ");
    }
}