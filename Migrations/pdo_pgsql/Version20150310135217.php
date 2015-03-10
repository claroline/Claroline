<?php

namespace Innova\CollecticielBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/10 01:52:19
 */
class Version20150310135217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment RENAME COLUMN commenttext TO comment_text
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_comment RENAME COLUMN comment_text TO commentText
        ");
    }
}