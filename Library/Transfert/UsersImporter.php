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

use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.user")
 */
class UsersImporter implements ImporterInterface
{
    public $types = array('yml');
    private $userManager;

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
        if (in_array($type, $this->types)) {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate(array $array)
    {

    }

    /**
     * @inheritdoc
     */
    public function import(array $users)
    {
        $array = array();

        foreach ($users as $user) {

            $indexedUser = array();

            foreach ($user as $element) {
                $indexedUser[] = $element;
            }

            $array[] = $indexedUser;
        }

        $this->userManager->importUsers($array);
    }
} 