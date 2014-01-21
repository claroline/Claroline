<?

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

class Updater021000
{
    private $container;
    private $logger;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateUsers();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }

    private function updateUsers()
    {
        $this->log('Updating users...');

        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $this->om->startFlushSuite();

        for ($i = 0, $count = count($users); $i < $count; ++$i) {

            $user = $users[$i];
            $this->log('updating ' . $user->getUsername() . '...');
            $user->setIsEnabled(true);
            $this->om->persist($user);

            if ($i % 200 === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();
    }
} 