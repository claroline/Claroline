<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/30 08:21:35
 */
class Version20161130082133 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response CHANGE mark mark DOUBLE PRECISION DEFAULT NULL
        ');

        $this->addSql('
            UPDATE ujm_response SET mark = NULL WHERE mark = -1
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            UPDATE ujm_response SET mark = -1 WHERE mark IS NULL
        ');

        $this->addSql('
            ALTER TABLE ujm_response CHANGE mark mark DOUBLE PRECISION NOT NULL
        ');
    }
}
