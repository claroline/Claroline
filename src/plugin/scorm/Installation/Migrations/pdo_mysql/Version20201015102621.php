<?php

namespace Claroline\ScormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/15 10:26:22
 */
class Version20201015102621 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            ADD progression DOUBLE PRECISION NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            DROP progression
        ');
    }
}
