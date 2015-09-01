<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tag_manager")
 */
class TagManager
{
    private $om;

    private $taggedItemRepo;
    private $tagRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->taggedItemRepo = $om->getRepository('ClarolineTagBundle:TaggedItem');
        $this->tagRepo = $om->getRepository('ClarolineTagBundle:Tag');
    }
}
