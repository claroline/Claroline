<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/29 09:21:57
 */
class Version20230129092146 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            DROP hash_time
        ');

        // merge organization managers and organization members table
        $this->addSql('
            INSERT INTO user_organization (user_id, organization_id, is_manager, is_main)
                SELECT a.user_id, a.organization_id, 1 AS is_manager, 0 AS is_main
                FROM claro_user_administrator AS a
                WHERE NOT EXISTS (
                    SELECT uo.*
                    FROM user_organization AS uo
                    WHERE uo.organization_id = a.organization_id
                      AND uo.user_id = a.user_id
                )
        ');

        $this->addSql('
            UPDATE user_organization AS uo
            SET uo.is_manager = 1
            WHERE EXISTS (
                SELECT a.*
                FROM claro_user_administrator AS a
                WHERE uo.organization_id = a.organization_id
                AND uo.user_id = a.user_id
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            ADD hash_time INT DEFAULT NULL
        ');
    }
}
