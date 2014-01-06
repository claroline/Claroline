<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/05 04:04:46
 */
class Version20131205160441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_api_client (
                id INT IDENTITY NOT NULL,
                random_id VARCHAR(255) NOT NULL,
                redirect_uris TEXT NOT NULL,
                secret VARCHAR(255) NOT NULL,
                allowed_grant_types TEXT NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_api_client.redirect_uris IS '(DC2Type:array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_api_client.allowed_grant_types IS '(DC2Type:array)'
        ");
        $this->addSql("
            CREATE TABLE claro_api_access_token (
                id INT IDENTITY NOT NULL,
                client_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                CONSTRAINT UNIQ_CE948285F37A13B UNIQUE (token),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CE9482819EB6921 ON claro_api_access_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE94828A76ED395 ON claro_api_access_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_refresh_token (
                id INT IDENTITY NOT NULL,
                client_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                CONSTRAINT UNIQ_B1292B905F37A13B UNIQUE (token),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B9019EB6921 ON claro_api_refresh_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B90A76ED395 ON claro_api_refresh_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_auth_code (
                id INT IDENTITY NOT NULL,
                client_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                redirect_uri TEXT NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                CONSTRAINT UNIQ_9DFA4575F37A13B UNIQUE (token),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA45719EB6921 ON claro_api_auth_code (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA457A76ED395 ON claro_api_auth_code (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_access_token
            ADD CONSTRAINT FK_CE9482819EB6921 FOREIGN KEY (client_id)
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_access_token
            ADD CONSTRAINT FK_CE94828A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token
            ADD CONSTRAINT FK_B1292B9019EB6921 FOREIGN KEY (client_id)
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token
            ADD CONSTRAINT FK_B1292B90A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code
            ADD CONSTRAINT FK_9DFA45719EB6921 FOREIGN KEY (client_id)
            REFERENCES claro_api_client (id)
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code
            ADD CONSTRAINT FK_9DFA457A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_api_access_token
            DROP FOREIGN KEY FK_CE9482819EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token
            DROP FOREIGN KEY FK_B1292B9019EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code
            DROP FOREIGN KEY FK_9DFA45719EB6921
        ");
        $this->addSql("
            DROP TABLE claro_api_client
        ");
        $this->addSql("
            DROP TABLE claro_api_access_token
        ");
        $this->addSql("
            DROP TABLE claro_api_refresh_token
        ");
        $this->addSql("
            DROP TABLE claro_api_auth_code
        ");
    }
}
