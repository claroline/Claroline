<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 05:36:36
 */
class Version20140623173635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                baseData TINYINT(1) NOT NULL, 
                mail TINYINT(1) NOT NULL, 
                phone TINYINT(1) NOT NULL, 
                sendMail TINYINT(1) NOT NULL, 
                sendMessage TINYINT(1) NOT NULL, 
                INDEX IDX_38AACF88D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation CHANGE last_date evaluation_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation CHANGE last_date lastest_evaluation_date DATETIME DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation CHANGE lastest_evaluation_date last_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation CHANGE evaluation_date last_date DATETIME DEFAULT NULL
        ");
    }
}