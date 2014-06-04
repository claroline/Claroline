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

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.manager.competence_manager")
 */
class CompetenceManager {

    private $om;
    private $security;
    private $rm;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "security"     = @DI\Inject("security.context"),
     *     "rm"           =  @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $security,
        RoleManager $rm
    )
    {
        $this->om = $om;
        $this->security = $security;
        $this->rm = $rm;
    }

    public function add(Competence $competence, $workspace = null)
    {
        if(!is_null($workspace)) {
            $this->checkUserIsAllowed('agenda', $workspace);
        }
        $competence->setIsplatform(true);
        $this->om->persist($competence);
        $this->om->flush();

    }

    private function checkUserIsAllowed($permission, AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 