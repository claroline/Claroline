<?php

namespace Claroline\TransferBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/19 07:25:46
 */
class Version20220219072544 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_import_file 
            ADD creator_id INT DEFAULT NULL, 
            CHANGE start_date createdAt DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_import_file 
            ADD CONSTRAINT FK_EA6FE9F161220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_EA6FE9F161220EA6 ON claro_import_file (creator_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_import_file 
            DROP FOREIGN KEY FK_EA6FE9F161220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_EA6FE9F161220EA6 ON claro_import_file
        ');
        $this->addSql('
            ALTER TABLE claro_import_file 
            DROP creator_id, 
            CHANGE createdAt start_date DATETIME DEFAULT NULL
        ');
    }
}
