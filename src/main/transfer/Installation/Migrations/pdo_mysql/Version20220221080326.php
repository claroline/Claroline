<?php

namespace Claroline\TransferBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/21 08:03:41
 */
class Version20220221080326 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_transfer_export (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                action VARCHAR(255) DEFAULT NULL, 
                status VARCHAR(255) DEFAULT NULL, 
                executionDate DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D64B84DDD17F50A6 (uuid), 
                INDEX IDX_D64B84DD82D40A1F (workspace_id), 
                INDEX IDX_D64B84DD61220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            RENAME TABLE claro_import_file TO claro_transfer_import
        ');
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_transfer_export
        ');
        $this->addSql('
            RENAME TABLE claro_transfer_import TO claro_import_file
        ');
    }
}
