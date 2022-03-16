<?php

namespace Icap\WikiBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/16 01:18:30
 */
class Version20220316131828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAA76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAA76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
