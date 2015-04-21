<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/21 03:00:41
 */
class Version20150421150040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD content_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP display_name
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD CONSTRAINT FK_60F9096584A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_60F9096584A0A3ED ON claro_tools (content_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP CONSTRAINT FK_60F9096584A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_60F9096584A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD display_name VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP content_id
        ");
    }
}