<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/22 08:26:34
 */
class Version20190522082628 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD scoreRule LONGTEXT DEFAULT NULL
        ');

        // populate new score rule
        $this->addSql('
            UPDATE ujm_exercise SET scoreRule = \'{"type": "none"}\' WHERE `type` = "survey"
        ');

        $this->addSql('
            UPDATE ujm_exercise SET scoreRule = \'{"type": "sum"}\' WHERE totalScoreOn IS NULL OR totalScoreOn = 0 OR totalScoreOn = "" AND `type` != "survey"
        ');

        $this->addSql('
            UPDATE ujm_exercise SET scoreRule = CONCAT(\'{"type": "sum", "total": \', totalScoreOn, \'}\') WHERE totalScoreOn != "" AND totalScoreOn > 0 AND  `type` != "survey"
        ');

        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP totalScoreOn
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP scoreRule
        ');

        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD totalScoreOn DOUBLE PRECISION NOT NULL
        ');
    }
}
