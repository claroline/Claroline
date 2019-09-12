<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/21 03:11:19
 */
class Version20190821151113 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step
            ADD slug VARCHAR(128)
        ');
        $this->addSql("
            UPDATE innova_step step SET slug = REGEXP_REPLACE(SUBSTR(step.title,1,100), '[^A-Za-z0-9]+', '-')
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step
            DROP slug
        ');
    }
}
