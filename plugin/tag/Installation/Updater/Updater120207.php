<?php

namespace Claroline\TagBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

/**
 * Uses UUID for tagged objects instead of auto ID.
 */
class Updater120207 extends Updater
{
    private $conn;

    public function __construct($container)
    {
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->log('Replace TaggedObjects IDs by UUIDs...');

        $this->log('Replace TaggedObjects : ResourceNode');
        $this->conn->query('
            UPDATE claro_tagbundle_tagged_object t
            LEFT JOIN claro_resource_node r ON (r.id = t.object_id AND t.object_class = "Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode")
            SET t.object_id = r.uuid
            WHERE r.id IS NOT NULL
        ');

        $this->log('Replace TaggedObjects : Workspace');
        $this->conn->query('
            UPDATE claro_tagbundle_tagged_object t
            LEFT JOIN claro_workspace w ON (w.id = t.object_id AND t.object_class = "Claroline\\CoreBundle\\Entity\\Workspace\\Workspace")
            SET t.object_id = w.uuid
            WHERE w.id IS NOT NULL
        ');

        $this->log('Replace TaggedObjects : User');
        $this->conn->query('
            UPDATE claro_tagbundle_tagged_object t
            LEFT JOIN claro_user u ON (u.id = t.object_id AND t.object_class = "Claroline\\CoreBundle\\Entity\\User")
            SET t.object_id = u.uuid
            WHERE u.id IS NOT NULL
        ');
    }
}
