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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashCardBundle\Entity\NoteType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @DI\Service("claroline.flashcard.note_type_manager")
 */
class NoteTypeManager
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
     * @param NoteType $noteType
     *
     * @return NoteType
     */
    public function create(NoteType $noteType)
    {
        $this->om->persist($noteType);
        foreach ($noteType->getFieldLabels() as $f) {
            $this->om->persist($f);
        }
        foreach ($noteType->getCardTypes() as $c) {
            $this->om->persist($c);
            foreach ($c->getQuestions() as $f) {
                $this->om->persist($f);
            }
            foreach ($c->getAnswers() as $f) {
                $this->om->persist($f);
            }
        }
        $this->om->flush();

        return $noteType;
    }

    /**
     * @param NoteType $noteType
     */
    public function delete(NoteType $noteType)
    {
        $this->om->remove($noteType);
        $this->om->flush();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function get($id)
    {
        $repoNoteType = $this->om->getRepository('ClarolineFlashCardBundle:NoteType');

        return $repoNoteType->findBy(['id' => $id], ['id' => 'ASC']);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $repoNoteType = $this->om->getRepository('ClarolineFlashCardBundle:NoteType');

        return $repoNoteType->findBy([], ['id' => 'ASC']);
    }
}
