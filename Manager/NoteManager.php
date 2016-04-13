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
use Claroline\FlashCardBundle\Entity\Note;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormView;

/**
 * @DI\Service("claroline.flashcard.note_manager")
 */
class NoteManager
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
     * @param Note $note
     * @return Note
     */
    public function create(Note $note)
    {
        foreach($note->getFieldValues() as $fieldValue) {
            $this->om->persist($fieldValue);
        }

        foreach($note->getCards() as $card) {
            $this->om->persist($card);
        }

        $this->om->persist($note);
        $this->om->flush();

        return $note;
    }

    /**
     * @param Note $note
     */
    public function delete(Note $note)
    {
        $this->om->remove($note);
        $this->om->flush();
    }
}
