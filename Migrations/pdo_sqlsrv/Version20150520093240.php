<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/20 09:32:41
 */
class Version20150520093240 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.published', 
            'breadcrumbs', 
            'COLUMN'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.breadcrumbs', 
            'published', 
            'COLUMN'
        ");
    }
}