<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20230329080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM claro_home_tab WHERE `type` = "training_catalog"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
