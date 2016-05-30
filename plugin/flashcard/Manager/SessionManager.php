<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\FlashCardBundle\Entity\Session;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @DI\Service("claroline.flashcard.session_manager")
 */
class SessionManager
{
    private $om;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param ObjectManager   $om
     * @param EngineInterface $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * @param Session $session
     *
     * @return Session
     */
    public function save(Session $session)
    {
        $this->om->persist($session);
        $this->om->flush();

        return $session;
    }

    /**
     * @param Session $session
     */
    public function delete(Session $session)
    {
        $this->om->remove($session);
        $this->om->flush();
    }

    /**
     * @param int $id
     *
     * @return Session
     */
    public function get($id)
    {
        $repo = $this->om->getRepository('ClarolineFlashCardBundle:Session');

        return $repo->find($id);
    }
}
