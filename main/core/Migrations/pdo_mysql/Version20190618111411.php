<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/06/18 11:14:15
 */
class Version20190618111411 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_D9028545A76ED395 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP is_access_date, 
            CHANGE user_id creator_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D902854561220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_D902854561220EA6 ON claro_workspace (creator_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D902854561220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_D902854561220EA6 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD is_access_date TINYINT(1) NOT NULL, 
            CHANGE creator_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ');
    }
}
