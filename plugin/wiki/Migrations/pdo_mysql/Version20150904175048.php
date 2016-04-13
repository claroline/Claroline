<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/04 05:50:49
 */
class Version20150904175048 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section CHANGE wiki_id wiki_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution CHANGE section_id section_id INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_contribution CHANGE section_id section_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section CHANGE wiki_id wiki_id INT NOT NULL
        ');
    }
}
