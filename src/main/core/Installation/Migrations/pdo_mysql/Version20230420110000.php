<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/15 07:09:30
 */
class Version20230420110000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // For performances reason, we will set the same mask for all resources,
        // this is the maximum rights found in platform atm, which is 255.
        // It is the mask for the resource which has the more custom actions (eg. quiz, blog)
        // For other resources extra bits will just be ignored by the decoder manager so it's ok to do it.
        $mask = 255;

        $this->addSql("
            UPDATE claro_resource_rights AS r
            LEFT JOIN claro_role AS ro ON (r.role_id = ro.id)
            SET r.mask = {$mask}
            WHERE ro.name LIKE 'ROLE_WS_MANAGER_%'
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
