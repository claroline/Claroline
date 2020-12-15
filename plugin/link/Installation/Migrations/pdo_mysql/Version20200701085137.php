<?php

namespace Claroline\LinkBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:51:38
 */
class Version20200701085137 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema)
    {
        // if not exists because the table was originally created by CoreBundle
        if (!$this->checkTableExists('claro_resource_shortcut', $this->connection)) {
            $this->addSql('
                CREATE TABLE IF NOT EXISTS claro_resource_shortcut (
                    id INT AUTO_INCREMENT NOT NULL, 
                    target_id INT NOT NULL, 
                    resourceNode_id INT DEFAULT NULL, 
                    INDEX IDX_5E7F4AB8158E0B66 (target_id), 
                    UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 (resourceNode_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE claro_resource_shortcut 
                ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE
            ');
            $this->addSql('
                ALTER TABLE claro_resource_shortcut 
                ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE
            ');
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_resource_shortcut
        ');
    }
}
