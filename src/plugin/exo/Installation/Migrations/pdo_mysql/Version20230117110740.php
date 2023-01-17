<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/17 11:07:51
 */
class Version20230117110740 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_cell 
            ADD shuffle TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_cell SET shuffle  = 0
        ');

        $this->addSql('
            ALTER TABLE ujm_hole 
            ADD shuffle TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_hole SET shuffle  = 0
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_cell 
            DROP shuffle
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            DROP shuffle
        ');
    }
}
