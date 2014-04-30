<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/31 03:17:06
 */
class Version20140331151703 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_public_profile_preferences (
                id SERIAL NOT NULL, 
                user_id INT DEFAULT NULL, 
                share_policy INT NOT NULL, 
                display_phone_number BOOLEAN NOT NULL, 
                display_email BOOLEAN NOT NULL, 
                allow_mail_sending BOOLEAN NOT NULL, 
                allow_message_sending BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5CF2A583A76ED395 ON claro_user_public_profile_preferences (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD CONSTRAINT FK_5CF2A583A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD public_url VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD has_tuned_public_url BOOLEAN NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_public_profile_preferences
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP public_url
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP has_tuned_public_url
        ");
    }
}