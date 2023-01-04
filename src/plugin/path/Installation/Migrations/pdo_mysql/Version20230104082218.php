<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/04 08:22:32
 */
class Version20230104082218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD resource_id INT DEFAULT NULL, 
            DROP modified
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F05489329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_CE19F05489329D25 ON innova_path (resource_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP FOREIGN KEY FK_CE19F05489329D25
        ');
        $this->addSql('
            DROP INDEX IDX_CE19F05489329D25 ON innova_path
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD modified TINYINT(1) NOT NULL, 
            DROP resource_id
        ');
    }
}
