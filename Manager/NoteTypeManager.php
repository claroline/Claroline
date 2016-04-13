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
use Claroline\FlashCardBundle\Entity\NoteType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormView;

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
     * @param ObjectManager     $om
     * @param EngineInterface   $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * @param NoteType $noteType
     * @return NoteType
     */
    public function create(NoteType $noteType)
    {
        $this->om->persist($noteType);
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
     * @return array
     */
    public function getAll()
    {
        $repoNoteType = $this->om->getRepository('ClarolineFlashCardBundle:NoteType');
        return $repoNoteType->findBy(array(), array("id" => 'ASC'));
    }
}
