<?php

namespace Icap\OAuthBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/09 03:09:48
 */
class Version20150709150946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__oauth_user CHANGE oauthId oauthId VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__oauth_user CHANGE oauthId oauthId INT NOT NULL
        ');
    }
}
