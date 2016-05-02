<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/7/15
 */

namespace Icap\SocialmediaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\NoteAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class NoteActionManager.
 *
 * @DI\Service("icap_socialmedia.manager.note_action")
 */
class NoteActionManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Icap\SocialmediaBundle\Repository\NoteActionRepository
     */
    protected $noteActionRepository;

    /**
     * @var \Claroline\CoreBundle\Repository\ResourceNodeRepository
     */
    protected $resourceNodeRepository;

    /**
     * @DI\InjectParams({
     *      "em"                = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->noteActionRepository = $em->getRepository('IcapSocialmediaBundle:NoteAction');
        $this->resourceNodeRepository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    public function getNote($noteId, User $user)
    {
        return $this->noteActionRepository->findOneBy(array(
            'id' => $noteId,
            'user' => $user,
        ));
    }

    /**
     * @param $resourceId
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNotesForPagination($resourceId, User $user)
    {
        return $this->noteActionRepository->findNotesForPagination($resourceId, $user);
    }

    public function createNote($resourceId, NoteAction $note)
    {
        $resourceNode = $this->resourceNodeRepository->find($resourceId);
        $note->setResource($resourceNode);
        $this->em->persist($note);
        $this->em->flush();
    }

    public function removeNote($noteId, User $user)
    {
        $this->noteActionRepository->removeNote($noteId, $user);
    }
}
