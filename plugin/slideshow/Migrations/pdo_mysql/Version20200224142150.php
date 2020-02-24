<?php

namespace Claroline\SlideshowBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/24 02:22:50
 */
class Version20200224142150 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_slide 
            ADD color VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_slide 
            DROP color
        ');
    }
}
