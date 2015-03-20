<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/20 02:22:32
 */
class Version20150320142231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question ALTER COLUMN title NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE ujm_question ALTER COLUMN description VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question ALTER COLUMN title NVARCHAR(255) COLLATE utf8_unicode_ci NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_question ALTER COLUMN description VARCHAR(MAX) COLLATE utf8_unicode_ci NOT NULL
        ");
    }
}