<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/29 11:26:38
 */
class Version20190829112632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_step
            ADD slug VARCHAR(128) NOT NULL
        ');
        $this->addSql("
             UPDATE ujm_step step SET slug = CONCAT(SUBSTR(step.title,1,100) , '-', step.id) WHERE step.title IS NOT NULL
        ");
        $this->addSql("
             UPDATE ujm_step step SET slug = CONCAT('step' , '-', step.entity_order, '-', step.id) WHERE step.title IS NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_step,
            DROP slug
        ');
    }
}
