<?php

namespace Innova\CollecticielBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/10 01:52:20
 */
class Version20150310135217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_collecticielbundle_comment.commenttext', 
            'comment_text', 
            'COLUMN'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_collecticielbundle_comment.comment_text', 
            'commentText', 
            'COLUMN'
        ");
    }
}
