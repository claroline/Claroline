<?php

namespace Icap\WikiBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/07 07:58:22
 */
class Version20200907075820 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            DROP FOREIGN KEY FK_781E6502A76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            DROP FOREIGN KEY FK_781E6502A76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
