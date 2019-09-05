<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/21 03:11:19
 */
class Version20190821151114 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_86F48567989D9B62 ON innova_step
        ');
        $this->addSql("
             UPDATE innova_step step SET slug = REGEXP_REPLACE(SUBSTR(step.title,1,100), '[^A-Za-z0-9]+', '-')
        ");
    }

    public function down(Schema $schema)
    {
    }
}
