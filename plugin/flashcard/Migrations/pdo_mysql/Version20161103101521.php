<?php

namespace Claroline\FlashCardBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/03 10:15:28
 */
class Version20161103101521 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value 
            ADD mimetype VARCHAR(255) NOT NULL, 
            ADD type_discr VARCHAR(255) NOT NULL, 
            ADD alt LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value 
            DROP mimetype, 
            DROP type_discr, 
            DROP alt
        ');
    }
}
