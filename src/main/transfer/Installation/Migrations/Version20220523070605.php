<?php

namespace Claroline\TransferBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 01:27:50
 */
final class Version20220523070605 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_transfer_export (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                action VARCHAR(255) DEFAULT NULL, 
                status VARCHAR(255) DEFAULT NULL, 
                file_format VARCHAR(255) NOT NULL, 
                extra LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                executionDate DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D64B84DDD17F50A6 (uuid), 
                INDEX IDX_D64B84DD82D40A1F (workspace_id), 
                INDEX IDX_D64B84DD61220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_transfer_import (
                id INT AUTO_INCREMENT NOT NULL, 
                file_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                log VARCHAR(255) DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                action VARCHAR(255) DEFAULT NULL, 
                status VARCHAR(255) DEFAULT NULL, 
                file_format VARCHAR(255) NOT NULL, 
                extra LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                executionDate DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_9895C54D17F50A6 (uuid), 
                INDEX IDX_9895C5493CB796C (file_id), 
                INDEX IDX_9895C5482D40A1F (workspace_id), 
                INDEX IDX_9895C5461220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            ADD CONSTRAINT FK_D64B84DD82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            ADD CONSTRAINT FK_D64B84DD61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            ADD CONSTRAINT FK_9895C5493CB796C FOREIGN KEY (file_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            ADD CONSTRAINT FK_9895C5482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            ADD CONSTRAINT FK_9895C5461220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            DROP FOREIGN KEY FK_D64B84DD82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            DROP FOREIGN KEY FK_D64B84DD61220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            DROP FOREIGN KEY FK_9895C5493CB796C
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            DROP FOREIGN KEY FK_9895C5482D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            DROP FOREIGN KEY FK_9895C5461220EA6
        ');
        $this->addSql('
            DROP TABLE claro_transfer_export
        ');
        $this->addSql('
            DROP TABLE claro_transfer_import
        ');
    }
}
