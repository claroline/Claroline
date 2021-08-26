<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/08/26 04:13:12
 */
class Version20210826161310 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_quota 
            ADD use_quotas TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_quota 
            DROP use_quotas
        ');
    }
}
