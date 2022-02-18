<?php

namespace Claroline\TransferBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/15 06:00:00
 */
class Version20220215060000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        $this->skipIf($this->checkTableExists('claro_import_file', $this->connection), 'Migration already executed.');

        $this->addSql('
            CREATE TABLE claro_import_file (
                id INT AUTO_INCREMENT NOT NULL, 
                file_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                log VARCHAR(255) DEFAULT NULL, 
                status VARCHAR(255) DEFAULT NULL, 
                action VARCHAR(255) DEFAULT NULL, 
                start_date DATETIME DEFAULT NULL, 
                executionDate DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_EA6FE9F1D17F50A6 (uuid), 
                INDEX IDX_EA6FE9F193CB796C (file_id), 
                INDEX IDX_EA6FE9F182D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE claro_import_file 
            ADD CONSTRAINT FK_EA6FE9F193CB796C FOREIGN KEY (file_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_import_file 
            ADD CONSTRAINT FK_EA6FE9F182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->checkTableExists('claro_import_file', $this->connection), 'Table not exists.');

        $this->addSql('
            ALTER TABLE claro_import_file 
            DROP FOREIGN KEY FK_EA6FE9F182D40A1F
        ');

        $this->addSql('
            ALTER TABLE claro_import_file 
            DROP FOREIGN KEY FK_EA6FE9F193CB796C
        ');
        $this->addSql('
            DROP TABLE claro_import_file
        ');
    }
}
