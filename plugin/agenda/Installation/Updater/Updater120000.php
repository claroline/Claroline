<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

class Updater120000 extends Updater
{
    private $container;
    /** @var Connection */
    private $conn;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function preUpdate()
    {
        try {
            $this->log('backing up the events...');
            $this->conn->query('CREATE TABLE claro_event_old  AS (SELECT * FROM claro_event)');
        } catch (\Exception $e) {
            $this->log('Coulnt backup forum subjects');
        }
    }

    public function postUpdate()
    {
        $this->log('restoring the events...');
        $sql = 'SELECT * FROM claro_event_old ';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $i = 0;

        foreach ($stmt->fetchAll() as $event) {
            $this->log('Restoring event dates for '.$event['title']);
            $new = $this->om->getRepository('ClarolineAgendaBundle:Event')->find($event['id']);

            if ($event['start_date']) {
                $new->setStart(\DateTime::createFromFormat('U', $event['start_date']));
            }

            if ($event['end_date']) {
                $new->setEnd(\DateTime::createFromFormat('U', $event['end_date']));
            }

            $this->om->persist($new);
            ++$i;

            if (0 === $i % 100) {
                $this->om->flush();
            }
        }

        $this->om->flush();
    }
}
