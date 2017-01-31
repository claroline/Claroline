<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/31 06:44:15
 */
class Version20170131184412 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_share 
            DROP FOREIGN KEY FK_238BD3071E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            DROP FOREIGN KEY FK_238BD307A76ED395
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD3071E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD307A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_share 
            DROP FOREIGN KEY FK_238BD307A76ED395
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            DROP FOREIGN KEY FK_238BD3071E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD307A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD3071E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
    }
}
