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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;

/**
 * @DI\Service("claroline.manager.text_manager")
 */
class TextManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *       "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function create($content, $title, User $user)
    {
        $revision = new Revision();
        $revision->setContent($content);
        $revision->setUser($user);
        $text = new Text();
        $text->setName($title);
        $revision->setText($text);
        $this->om->persist($text);
        $this->om->persist($revision);
        $this->om->flush();

        return $text;
    }
}
