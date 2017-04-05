<?php

namespace Claroline\LdapBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/31 11:44:01
 */
class Version20170331114351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_ldap_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ldapId VARCHAR(255) NOT NULL, 
                serverName VARCHAR(255) NOT NULL, 
                INDEX IDX_6C1E56DCA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_ldap_user 
            ADD CONSTRAINT FK_6C1E56DCA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            DELETE FROM claro_admin_tools WHERE name = 'LDAP'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_ldap_user
        ');
        $this->addSql("
            INSERT INTO claro_admin_tools (name, class, plugin_id)
            SELECT 'LDAP', 'database', id FROM claro_plugin WHERE short_name = 'LdapBundle'
        ");
    }
}
