<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/13 07:20:53
 */
class Version20200913072051 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__location 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD address_street1 VARCHAR(255) DEFAULT NULL, 
            CHANGE boxnumber address_street2 VARCHAR(255) DEFAULT NULL, 
            CHANGE pc address_postal_code VARCHAR(255) DEFAULT NULL, 
            CHANGE town address_city VARCHAR(255) DEFAULT NULL, 
            ADD address_state VARCHAR(255) DEFAULT NULL, 
            CHANGE country address_country VARCHAR(255) DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro__location SET address_street1 = TRIM(CONCAT(streetNumber, " ", street))
        ');

        $this->addSql('
            ALTER TABLE claro__location
            DROP street, 
            DROP streetNumber
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__location 
            ADD street VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD streetNumber VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE address_street2 boxNumber VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE address_postal_code pc VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE address_city town VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE address_country country VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP description, 
            DROP thumbnail, 
            DROP poster, 
            DROP address_street1, 
            DROP address_state
        ');
    }
}
