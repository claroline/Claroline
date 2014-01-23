<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/23 09:36:26
 */
class Version20140123093623 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_478C586179F0D498
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_icon AS 
            SELECT id, 
            shortcut_id, 
            mimeType, 
            is_shortcut, 
            relative_url 
            FROM claro_resource_icon
        ");
        $this->addSql("
            DROP TABLE claro_resource_icon
        ");
        $this->addSql("
            CREATE TABLE claro_resource_icon (
                id INTEGER NOT NULL, 
                shortcut_id INTEGER DEFAULT NULL, 
                mimeType VARCHAR(255) NOT NULL, 
                is_shortcut BOOLEAN NOT NULL, 
                relative_url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_478C586179F0D498 FOREIGN KEY (shortcut_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_icon (
                id, shortcut_id, mimeType, is_shortcut, 
                relative_url
            ) 
            SELECT id, 
            shortcut_id, 
            mimeType, 
            is_shortcut, 
            relative_url 
            FROM __temp__claro_resource_icon
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_icon
        ");
        $this->addSql("
            CREATE INDEX IDX_478C586179F0D498 ON claro_resource_icon (shortcut_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            ADD COLUMN icon_location VARCHAR(255) DEFAULT NULL
        ");
    }
}