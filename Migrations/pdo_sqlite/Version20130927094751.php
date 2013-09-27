<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/27 09:47:51
 */
class Version20130927094751 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_2D4590E5A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2D4590E5D96C566B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_user2path AS
            SELECT id,
            user_id,
            path_id,
            status
            FROM innova_user2path
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                path_id INTEGER NOT NULL,
                status INTEGER NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id)
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id)
                REFERENCES innova_path (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_user2path (id, user_id, path_id, status)
            SELECT id,
            user_id,
            path_id,
            status
            FROM __temp__innova_user2path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_user2path
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_2D4590E5A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2D4590E5D96C566B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_user2path AS
            SELECT id,
            user_id,
            path_id,
            status
            FROM innova_user2path
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                path_id INTEGER NOT NULL,
                status INTEGER NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id)
                REFERENCES claro_user (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id)
                REFERENCES innova_path (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_user2path (id, user_id, path_id, status)
            SELECT id,
            user_id,
            path_id,
            status
            FROM __temp__innova_user2path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_user2path
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
    }
}
