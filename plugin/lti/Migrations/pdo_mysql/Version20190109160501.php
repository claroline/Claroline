<?php

namespace UJM\LtiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/01/09 04:05:02
 */
class Version20190109160501 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_lti_app 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_lti_app SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EEDB962ED17F50A6 ON ujm_lti_app (uuid)
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_resource 
            ADD ratio DOUBLE PRECISION DEFAULT NULL, 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE ltiApp_id ltiApp_id INT DEFAULT NULL
        ');
        $this->addSql('
            UPDATE ujm_lti_resource SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_43618A03D17F50A6 ON ujm_lti_resource (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_EEDB962ED17F50A6 ON ujm_lti_app
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_app 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_43618A03D17F50A6 ON ujm_lti_resource
        ');
        $this->addSql('
            ALTER TABLE ujm_lti_resource 
            DROP ratio, 
            DROP uuid, 
            CHANGE ltiApp_id ltiApp_id INT NOT NULL
        ');
    }
}
