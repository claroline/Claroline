<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251006080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // delete personal roles without a user
        $this->addSql('
            DELETE r 
            FROM claro_role AS r 
            WHERE r.entity_type = "user" 
            AND NOT EXISTS ( 
                SELECT role_id 
                FROM claro_user_role AS ur 
                WHERE r.id = ur.role_id 
            )
        ');

        // delete personal role linked to a deleted user
        $this->addSql('
            DELETE r 
            FROM `claro_role` AS r 
            WHERE r.entity_type = "user" 
            AND EXISTS ( 
                SELECT role_id 
                FROM claro_user AS u 
                LEFT JOIN claro_user_role AS ur ON (ur.user_id = u.id) 
                WHERE u.is_removed = 1 AND r.id = ur.role_id 
            )
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
