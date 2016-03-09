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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Entity\Result;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @DI\Service("claroline.flashcard.flashcard_manager")
 */
class FlashCardManager
{
    private $om;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param ObjectManager     $om
     * @param EngineInterface   $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * Creates a flashcard resource.
     *
     * @param FlashCard $flashcard
     * @return FlashCard
     */
    public function create(FlashCard $flashcard)
    {
        $this->om->persist($flashcard);
        $this->om->flush();

        return $flashcard;
    }

    /**
     * Deletes a flashcard resource.
     *
     * @param FlashCard $flashcard
     */
    public function delete(FlashCard $flashcard)
    {
        $this->om->remove($flashcard);
        $this->om->flush();
    }
}
