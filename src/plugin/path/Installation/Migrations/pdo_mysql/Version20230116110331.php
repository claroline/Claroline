<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/16 11:03:41
 */
class Version20230116110331 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD end_back_target_id INT DEFAULT NULL, 
            DROP end_back_target
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F05448FD0A1B FOREIGN KEY (end_back_target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_CE19F05448FD0A1B ON innova_path (end_back_target_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP FOREIGN KEY FK_CE19F05448FD0A1B
        ');
        $this->addSql('
            DROP INDEX IDX_CE19F05448FD0A1B ON innova_path
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD end_back_target LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP end_back_target_id
        ');
    }
}
