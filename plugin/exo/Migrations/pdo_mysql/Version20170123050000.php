<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/23 05:00:21
 */
class Version20170123050000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise CHANGE show_feedback show_feedback TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_picture 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_picture SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_88AACC8AD17F50A6 ON ujm_picture (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise CHANGE show_feedback show_feedback TINYINT(1) DEFAULT NULL
        ');
        $this->addSql('
            DROP INDEX UNIQ_88AACC8AD17F50A6 ON ujm_picture
        ');
        $this->addSql('
            ALTER TABLE ujm_picture 
            DROP uuid
        ');
    }
}
