<?php

namespace UJM\LtiBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:57:05
 */
class Version20190109160501 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_lti_app (
                id INT AUTO_INCREMENT NOT NULL, 
                url LONGTEXT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                appkey LONGTEXT DEFAULT NULL, 
                secret LONGTEXT DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_EEDB962ED17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_ltiapp_workspace (
                ltiapp_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_7FB6D142A22F70CC (ltiapp_id), 
                INDEX IDX_7FB6D14282D40A1F (workspace_id), 
                PRIMARY KEY(ltiapp_id, workspace_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_lti_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                openInNewTab TINYINT(1) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                ltiApp_id INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_43618A03D17F50A6 (uuid), 
                INDEX IDX_43618A03A58375FA (ltiApp_id), 
                UNIQUE INDEX UNIQ_43618A03B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_ltiapp_workspace 
            ADD CONSTRAINT FK_7FB6D142A22F70CC FOREIGN KEY (ltiapp_id) 
            REFERENCES ujm_lti_app (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_ltiapp_workspace 
            ADD CONSTRAINT FK_7FB6D14282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_resource 
            ADD CONSTRAINT FK_43618A03A58375FA FOREIGN KEY (ltiApp_id) 
            REFERENCES ujm_lti_app (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_resource 
            ADD CONSTRAINT FK_43618A03B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_ltiapp_workspace 
            DROP FOREIGN KEY FK_7FB6D142A22F70CC
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_resource 
            DROP FOREIGN KEY FK_43618A03A58375FA
        ');
        $this->addSql('
            DROP TABLE ujm_lti_app
        ');
        $this->addSql('
            DROP TABLE ujm_ltiapp_workspace
        ');
        $this->addSql('
            DROP TABLE ujm_lti_resource
        ');
    }
}
