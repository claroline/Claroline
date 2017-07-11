<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Role;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater090200 extends Updater
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->syncUserRoles();
    }

    /**
     * Resynchronize user roles with the current username.
     */
    private function syncUserRoles()
    {
        $this->log('Synchronize user roles with the current username...');
        $this->connection
            ->prepare('
                UPDATE claro_role AS r
                LEFT JOIN claro_user_role AS ur ON (r.id = ur.role_id)
                LEFT JOIN claro_user AS u ON (ur.user_id = u.id)
                SET
                    r.name = CONCAT("ROLE_USER_", UPPER(u.username)),
                    r.translation_key = u.username
                WHERE r.type = :type
                  AND u.id IS NOT NULL
            ')
            ->execute([
                'type' => Role::USER_ROLE,
            ]);
    }
}
