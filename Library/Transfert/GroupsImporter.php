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

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.group")
 */
class GroupsImporter implements ImporterInterface
{
    private $groupManager;
    public $types = array('yml');

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *   "groupManager" = @DI\Inject("claroline.manager.group_manager")
     * })
     */

    public function __construct(
        GroupManager $groupManager
    )
    {
        $this->groupManager = $groupManager;
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
    public function import(array $groups)
    {

    }
} 