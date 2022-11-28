<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/12/01 02:54:33
 */
class Version20221201145416 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_role 
            ADD description LONGTEXT DEFAULT NULL, 
            CHANGE is_read_only is_locked TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_role 
            CHANGE is_locked is_read_only TINYINT(1) NOT NULL, 
            DROP description
        ');
    }
}
