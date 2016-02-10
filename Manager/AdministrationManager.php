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
use Claroline\CoreBundle\Entity\UserAdminAction;

/**
 * @DI\Service("claroline.manager.administration_manager")
 */
class AdministrationManager
{
    private $om;
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository('Claroline\CoreBundle\Entity\UserAdminAction');
    }

    public function addDefaultUserAdminActions()
    {
        $adminActions = array(
            array('edit', 'fa-pencil', 'edit'),
            array('show_workspaces', 'fa-book', 'show_workspaces')
        );

        foreach ($adminActions as $adminAction) {

            if (!$this->repo->findOneByToolName($adminAction[0])) {
                $aa = new UserAdminAction();
                $aa->setToolName($adminAction[0]);
                $aa->setClass($adminAction[1]);
                $aa->setDisplayedName($adminAction[2]);

                $this->om->persist($aa);
            }
        }

        $this->om->flush();
    }
}
