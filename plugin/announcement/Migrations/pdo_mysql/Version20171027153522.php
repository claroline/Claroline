<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/10/27 03:35:24
 */
class Version20171027153522 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_announcement_roles (
                announcement_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_4075322B913AEA17 (announcement_id), 
                INDEX IDX_4075322BD60322AC (role_id), 
                PRIMARY KEY(announcement_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_roles 
            ADD CONSTRAINT FK_4075322B913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_roles 
            ADD CONSTRAINT FK_4075322BD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_announcement_roles
        ');
    }
}
