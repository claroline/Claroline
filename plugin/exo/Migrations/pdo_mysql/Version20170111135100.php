<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/11 01:51:02
 */
class Version20170111135100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_label
            ADD uuid VARCHAR(36) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_label SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C22A1EB5D17F50A6 ON ujm_label (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_proposal
            ADD uuid VARCHAR(36) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_proposal SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_2672B44BD17F50A6 ON ujm_proposal (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_C22A1EB5D17F50A6 ON ujm_label
        ');

        $this->addSql('
            ALTER TABLE ujm_label
            DROP uuid
        ');

        $this->addSql('
            DROP INDEX UNIQ_2672B44BD17F50A6 ON ujm_proposal
        ');

        $this->addSql('
            ALTER TABLE ujm_proposal
            DROP uuid
        ');
    }
}
