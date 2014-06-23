<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                baseData BIT NOT NULL, 
                mail BIT NOT NULL, 
                phone BIT NOT NULL, 
                sendMail BIT NOT NULL, 
                sendMessage BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C73878F1D60322AC ON claro_public_profile_preference (role_id)
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