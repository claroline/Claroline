<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/05/24 10:53:32
 */
class Version20180524105331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_announcements_send (
                id INT AUTO_INCREMENT NOT NULL,
                announcement_id INT DEFAULT NULL,
                data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)',
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_7C739377D17F50A6 (uuid),
                INDEX IDX_7C739377913AEA17 (announcement_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_announcements_send
            ADD CONSTRAINT FK_7C739377913AEA17 FOREIGN KEY (announcement_id)
            REFERENCES claro_announcement (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_announcements_send
        ');
    }
}
