<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/05 04:04:45
 */
class Version20131205160441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_api_client (
                id INT IDENTITY NOT NULL,
                random_id NVARCHAR(255) NOT NULL,
                redirect_uris VARCHAR(MAX) NOT NULL,
                secret NVARCHAR(255) NOT NULL,
                allowed_grant_types VARCHAR(MAX) NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_api_access_token (
                id INT IDENTITY NOT NULL,
                client_id INT NOT NULL,
                user_id INT,
                token NVARCHAR(255) NOT NULL,
                expires_at INT,
                scope NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE948285F37A13B ON claro_api_access_token (token)
            WHERE token IS NOT NULL
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
                user_id INT,
                token NVARCHAR(255) NOT NULL,
                expires_at INT,
                scope NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B1292B905F37A13B ON claro_api_refresh_token (token)
            WHERE token IS NOT NULL
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
                user_id INT,
                token NVARCHAR(255) NOT NULL,
                redirect_uri VARCHAR(MAX) NOT NULL,
                expires_at INT,
                scope NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_9DFA4575F37A13B ON claro_api_auth_code (token)
            WHERE token IS NOT NULL
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
            DROP CONSTRAINT FK_CE9482819EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_refresh_token
            DROP CONSTRAINT FK_B1292B9019EB6921
        ");
        $this->addSql("
            ALTER TABLE claro_api_auth_code
            DROP CONSTRAINT FK_9DFA45719EB6921
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
