<?php

namespace Claroline\NotificationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/05/26 08:10:20
 */
final class Version20240526080915 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_notification (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL,
                user_id INT DEFAULT NULL, 
                message VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_856AFB35D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE claro_notification 
            ADD CONSTRAINT FK_856AFB35A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_856AFB35A76ED395 ON claro_notification (user_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_notification 
            DROP FOREIGN KEY FK_856AFB35A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_856AFB35A76ED395 ON claro_notification
        ');
        
        $this->addSql('
            DROP TABLE claro_notification
        ');
    }
}
