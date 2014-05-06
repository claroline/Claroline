<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/31 03:17:08
 */
class Version20140331151703 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_public_profile_preferences (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                share_policy INT NOT NULL, 
                display_phone_number BIT NOT NULL, 
                display_email BIT NOT NULL, 
                allow_mail_sending BIT NOT NULL, 
                allow_message_sending BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5CF2A583A76ED395 ON claro_user_public_profile_preferences (user_id) 
            WHERE user_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD CONSTRAINT FK_5CF2A583A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD public_url NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD has_tuned_public_url BIT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url) 
            WHERE public_url IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_public_profile_preferences
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN public_url
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN has_tuned_public_url
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EB8D2852181F3A64'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT UNIQ_EB8D2852181F3A64 ELSE 
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ");
    }
}