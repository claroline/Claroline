<?php

namespace Claroline\TagBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/22 07:47:06
 */
class Version20171122074704 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object CHANGE object_id object_id VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object CHANGE object_id object_id INT NOT NULL
        ');
    }
}
