<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use JMS\DiExtraBundle\Annotation as DI;
/**
 * @DI\Service("claroline.importer.workspace_properties")
 */
class WorkspacePropertiesImporter implements ImporterInterface{

    private $userManager;
    public $types = array('xml');

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *  "userManager"        = @DI\Inject("claroline.manager.user_manager")
     * })
     */
     public function __construct(
         UserManager $userManager
     )
     {
         $this->userManager = $userManager;
     }

    /**
     * @inheritdoc
     */
    public function supports($type)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $array)
    {
        if (isset($array['owner'])) {
            $username = $array['owner'];
            $user = $this->userManager->getUserByUsername($username);

            if (!$user) {
                return $error[] = "The user {$username} does not exists";
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function import(array $array)
    {
        if (isset($array['owner'])) {
            $username = $array['owner'];
            $user = $this->userManager->getUserByUsername($username);
        }

        $workspace = new SimpleWorkspace();
        $workspace->setCreator($user);
        $workspace->setName($array['name']);
        $workspace->setCode($array['code']);
        $workspace->setDisplayable($array['visible']);
        $workspace->setSelfRegistration($array['selfRegistration']);
        $workspace->setSelfUnregistration($array['selfUnregistration']);
    }
} 