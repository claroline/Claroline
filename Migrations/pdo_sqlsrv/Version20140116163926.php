<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/16 04:39:27
 */
class Version20140116163926 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_content_translation (
                id INT IDENTITY NOT NULL, 
                locale NVARCHAR(8) NOT NULL, 
                object_class NVARCHAR(255) NOT NULL, 
                field NVARCHAR(32) NOT NULL, 
                foreign_key NVARCHAR(64) NOT NULL, 
                content VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX content_translation_idx ON claro_content_translation (
                locale, object_class, field, foreign_key
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_content_translation
        ");
    }
}