<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/12/16 10:48:03
 */
class Version20191216104801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F7E3C61F9
        ');
        $this->addSql('
            DROP INDEX IDX_97FAB91F7E3C61F9 ON claro_log
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP owner_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_log 
            ADD owner_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ');
    }
}
