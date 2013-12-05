<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                random_id VARCHAR(255) NOT NULL, 
                redirect_uris TEXT NOT NULL COMMENT '(DC2Type:array)', 
                secret VARCHAR(255) NOT NULL, 
                allowed_grant_types TEXT NOT NULL COMMENT '(DC2Type:array)', 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_api_access_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                expires_at INT DEFAULT NULL, 
                `scope` VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_CE948285F37A13B (token), 
                INDEX IDX_CE9482819EB6921 (client_id), 
                INDEX IDX_CE94828A76ED395 (user_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_api_refresh_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                expires_at INT DEFAULT NULL, 
                `scope` VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_B1292B905F37A13B (token), 
                INDEX IDX_B1292B9019EB6921 (client_id), 
                INDEX IDX_B1292B90A76ED395 (user_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_api_auth_code (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                redirect_uri TEXT NOT NULL, 
                expires_at INT DEFAULT NULL, 
                `scope` VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_9DFA4575F37A13B (token), 
                INDEX IDX_9DFA45719EB6921 (client_id), 
                INDEX IDX_9DFA457A76ED395 (user_id)
            )
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