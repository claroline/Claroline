<?php

namespace Claroline\RssBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 02:58:46
 */
final class Version20190206142940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_rss_feed (
                id INT AUTO_INCREMENT NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2BF44A06D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_2BF44A06B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_rss_feed 
            ADD CONSTRAINT FK_2BF44A06B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_rss_feed 
            DROP FOREIGN KEY FK_2BF44A06B87FAB32
        ');
        $this->addSql('
            DROP TABLE claro_rss_feed
        ');
    }
}
