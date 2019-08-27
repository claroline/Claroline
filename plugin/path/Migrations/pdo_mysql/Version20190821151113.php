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
            ADD slug VARCHAR(128) NOT NULL
        ');
        $this->addSql("
             UPDATE innova_step step SET slug = CONCAT(SUBSTR(step.title,1,100) , '-', step.id) WHERE step.slug = NULL
        ");
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_86F48567989D9B62 ON innova_step (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_86F48567989D9B62 ON innova_step
        ');
        $this->addSql('
            ALTER TABLE innova_step
            DROP slug,
        ');
    }
}
