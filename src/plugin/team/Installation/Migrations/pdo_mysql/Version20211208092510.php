<?php

namespace Claroline\TeamBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/08 09:25:12
 */
class Version20211208092510 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE58042C94069F
        ');

        // set node ids instead of directory ids
        $this->addSql('
            UPDATE claro_team AS t
            LEFT JOIN claro_directory AS d ON t.directory_id = d.id
            SET t.directory_id = d.resourceNode_id
            WHERE t.directory_id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE58042C94069F
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_directory (id) 
            ON DELETE SET NULL
        ');
    }
}
