<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/25 11:58:53
 */
class Version20140825115851 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_content2type 
            ADD COLUMN collapse BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1A2084EF84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFC54C8C93
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content2type AS 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_content2type
        ");
        $this->addSql("
            DROP TABLE claro_content2type
        ");
        $this->addSql("
            CREATE TABLE claro_content2type (
                id INTEGER NOT NULL, 
                content_id INTEGER NOT NULL, 
                type_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1A2084EF84A0A3ED FOREIGN KEY (content_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFC54C8C93 FOREIGN KEY (type_id) 
                REFERENCES claro_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_content2type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFE9583FF0 FOREIGN KEY (back_id) 
                REFERENCES claro_content2type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_content2type (
                id, content_id, type_id, next_id, back_id, 
                size
            ) 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_content2type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content2type
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EF84A0A3ED ON claro_content2type (content_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFC54C8C93 ON claro_content2type (type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFAA23F6C8 ON claro_content2type (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFE9583FF0 ON claro_content2type (back_id)
        ");
    }
}