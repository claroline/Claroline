<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/17 05:37:41
 */
class Version20140617173739 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_public_profile_preference (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                baseData TINYINT(1) NOT NULL, 
                mail TINYINT(1) NOT NULL, 
                phone TINYINT(1) NOT NULL, 
                sendMail TINYINT(1) NOT NULL, 
                sendMessage TINYINT(1) NOT NULL, 
                INDEX IDX_C73878F1D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_public_profile_preference 
            ADD CONSTRAINT FK_C73878F1D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_public_profile_preference
        ");
    }
}