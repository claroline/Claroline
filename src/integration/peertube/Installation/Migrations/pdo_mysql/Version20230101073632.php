<?php

namespace Claroline\PeerTubeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/01 07:36:37
 */
class Version20230101073632 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_peertube_video (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                server VARCHAR(255) NOT NULL, 
                originalUuid VARCHAR(255) NOT NULL, 
                shortUuid VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_77719BDBD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_77719BDBB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE claro_peertube_video 
            ADD CONSTRAINT FK_77719BDBB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_peertube_video
        ');
    }
}
