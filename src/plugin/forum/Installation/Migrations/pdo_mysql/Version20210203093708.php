<?php

namespace Claroline\ForumBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/02/03 09:37:09
 */
class Version20210203093708 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum 
            ADD messageOrder VARCHAR(255) DEFAULT "ASC" NOT NULL, 
            ADD expandComments TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum 
            DROP messageOrder, 
            DROP expandComments
        ');
    }
}
