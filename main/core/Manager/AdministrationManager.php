<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Action\AdditionalAction;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.administration_manager")
 */
class AdministrationManager
{
    use LoggableTrait;

    private $om;
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
        $this->repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Action\AdditionalAction');
    }

    public function addDefaultAdditionalActions()
    {
        $actions = array(
            array('edit', 'fa-pencil', 'edit', 'admin_user_action'),
            array('show_workspaces', 'fa-book', 'show_workspaces', 'admin_user_action'),
        );

        foreach ($actions as $action) {
            if (count($this->repo->findBy(array('action' => $action[0], 'type' => $action[3]))) === 0) {
                $this->log("Adding action {$action[0]} {$action[3]}...");
                $aa = new AdditionalAction();
                $aa->setAction($action[0]);
                $aa->setClass($action[1]);
                $aa->setDisplayedName($action[2]);
                $aa->setType($action[3]);

                $this->om->persist($aa);
            }
        }

        $this->om->flush();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
