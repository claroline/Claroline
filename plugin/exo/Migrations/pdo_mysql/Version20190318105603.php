<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/03/18 10:56:08
 */
class Version20190318105603 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question
            DROP FOREIGN KEY FK_2F60697712469DE2
        ');
        $this->addSql('
            DROP INDEX IDX_2F60697712469DE2 ON ujm_question
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            DROP category_id,
            DROP model
        ');
        $this->addSql('
            ALTER TABLE ujm_step CHANGE pick pick LONGTEXT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question
            ADD category_id INT DEFAULT NULL,
            ADD model TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            ADD CONSTRAINT FK_2F60697712469DE2 FOREIGN KEY (category_id)
            REFERENCES ujm_category (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_2F60697712469DE2 ON ujm_question (category_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_step CHANGE pick pick INT NOT NULL
        ');
    }
}
