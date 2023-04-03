<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/03/31 08:18:00
 */
class Version20230331081748 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349EE7F5384
        ');
        $this->addSql('
            DROP INDEX IDX_3359D349EE7F5384 ON claro_cursusbundle_course
        ');

        $this->addSql('
            UPDATE claro_cursusbundle_course SET workspace_id = workspace_model_id WHERE workspace_model_id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP workspace_model_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD workspace_model_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D349EE7F5384 ON claro_cursusbundle_course (workspace_model_id)
        ');
    }
}
