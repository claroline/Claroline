<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/01 04:45:57
 */
class Version20140701164555 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX activity_rule_unique_action_resource_type ON claro_activity_rule_action (log_action, resource_type_id) 
            WHERE log_action IS NOT NULL 
            AND resource_type_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'activity_rule_unique_action_resource_type'
            ) 
            ALTER TABLE claro_activity_rule_action 
            DROP CONSTRAINT activity_rule_unique_action_resource_type ELSE 
            DROP INDEX activity_rule_unique_action_resource_type ON claro_activity_rule_action
        ");
    }
}