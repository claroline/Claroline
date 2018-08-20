<?php

namespace Claroline\AuthenticationBundle\Migrations\pdo_mysql;

use Claroline\CoreBundle\Library\Migration\ConditionalMigrationTrait;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/09 01:32:55
 */
class Version20180719133254 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        // If table exists from previous Oauth plugin, just rename it
        $tableExists = $this->checkTableExists('icap__oauth_user', $this->connection);
        if ($tableExists) {
            $this->addSql(
                'RENAME TABLE icap__oauth_user TO claro_oauth_user'
            );

            return;
        }

        // Otherwise create new table
        $this->addSql('
            CREATE TABLE claro_oauth_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                oauthId VARCHAR(255) NOT NULL, 
                service VARCHAR(255) NOT NULL, 
                INDEX IDX_301D647CA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_oauth_user 
            ADD CONSTRAINT FK_301D647CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_oauth_user
        ');
    }
}
