<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/12 11:36:15
 */
class Version20150212113613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D3477F405E237E06 ON hevinci_scale (name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX hevinci_scale.UNIQ_D3477F405E237E06
        ");
    }
}