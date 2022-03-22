<?php

namespace Claroline\ScormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/21 04:04:01
 */
class Version20220321160359 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            DROP FOREIGN KEY FK_2627E972A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking CHANGE user_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            ADD CONSTRAINT FK_2627E972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            DROP FOREIGN KEY FK_2627E972A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking CHANGE user_id user_id INT NOT NULL
        ');
    }
}
