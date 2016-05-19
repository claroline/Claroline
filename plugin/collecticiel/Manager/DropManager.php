<?php

namespace Innova\CollecticielBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogDropEndEvent;
use Innova\CollecticielBundle\Event\Log\LogDropStartEvent;

/**
 * @DI\Service("innova.manager.drop_manager")
 */
class DropManager
{
    private $container;
    private $em;
    private $eventDispatcher;
    private $roleManager;
    private $dropRepo;
    private $commentRepo;
    private $receiptRepo;
    private $documentRepo;

    /**
     * @DI\InjectParams({
     *     "container"       = @DI\Inject("service_container"),
     *     "em"              = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct($container, $em, $eventDispatcher, $roleManager)
    {
        $this->container = $container;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;
        $this->dropRepo = $this->em->getRepository('InnovaCollecticielBundle:Drop');
        $this->commentRepo = $this->em->getRepository('InnovaCollecticielBundle:Comment');
        $this->documentRepo = $this->em->getRepository('InnovaCollecticielBundle:Document');
        $this->receiptRepo = $this->em->getRepository('InnovaCollecticielBundle:ReturnReceipt');
    }

    public function create(Dropzone $dropzone, User $user)
    {
        $drop = new Drop();

        $number = ($this->dropRepo->getLastNumber($dropzone) + 1);
        $drop->setNumber($number);
        $drop->setUser($user);
        $drop->setDropzone($dropzone);
        $drop->setFinished(false);

        $this->em->persist($drop);
        $this->em->flush();
        $this->em->refresh($drop);

        return $drop;
    }

    public function saveDrop(Drop $drop)
    {
        $em->persist($drop);
        $em->flush();

        $this->eventDispatcher->dispatch('log', new LogDropEndEvent($drop->getDropzone(), $drop, $this->roleManager));
    }

    public function isDropFinished(Dropzone $dropzone, User $user)
    {
        return $isFinished = $this->dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => true)) !== null
            ? true
            : false;
    }

    public function getDrop(Dropzone $dropzone, User $user)
    {
        // on récupère le drop existant ou on le créé s'il n'existe pas.
        $drop = $this->dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => false));
        if ($drop === null) {
            $drop = $this->create($dropzone, $user);
            $this->eventDispatcher->dispatch('log', new LogDropStartEvent($dropzone, $drop));
        }

        return $drop;
    }

    public function getDropTeacherComments(Drop $drop)
    {
        $teacherComments = array();
        $dropzone = $drop->getDropzone();
        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();
            $teacherComments[$documentId] = 0;
            $userComments = $this->commentRepo->teacherCommentDocArray($document);
            $foundAdminComment = false;
            for ($indice = 0; $indice < count($userComments); ++$indice) {
                $workspace = $dropzone->getResourceNode()->getWorkspace();
                $role = $this->roleManager->getWorkspaceRolesForUser($userComments[$indice]->getUser(), $workspace);
                for ($i = 0; $i < count($role); ++$i) {
                    $roleName = $role[$i]->getName();
                    if (strpos('_'.$roleName, 'ROLE_WS_MANAGER') === 1 && !$foundAdminComment) {
                        $teacherComments[$documentId] = 1;
                        $foundAdminComment = true;
                    }
                }
            }
        }

        return $teacherComments;
    }

    public function getReturnReceipts(Drop $drop)
    {
        $returnReceipts = array();
        foreach ($drop->getDocuments() as $document) {
            $returnReceiptType = $this->receiptRepo->doneReturnReceiptForOneDocument($document);
            if (!empty($returnReceiptType)) {
                $returnReceipts[$document->getId()] = $returnReceiptType[0]->getReturnReceiptType()->getId();
            } else {
                $returnReceipts[$document->getId()] = 0;
            }
        }

        return $returnReceipts;
    }

    public function getDroppedDocsByUserCount(Dropzone $dropzone)
    {
        $userDocDroppedCount = array();

        foreach ($dropzone->getDrops() as $drop) {
            $user = $drop->getUser();
            $nbDocDropped = $this->documentRepo->countDocSubmissions($user, $dropzone);
            $userDocDroppedCount[$user->getId()] = $nbDocDropped;
        }

        return $userDocDroppedCount;
    }

    public function getRequestByUserCount(Dropzone $dropzone)
    {
        $userRequestCount = array();

        foreach ($dropzone->getDrops() as $drop) {
            $user = $drop->getUser();
            $nbAdressedRequests = $this->documentRepo->countTextToRead($user, $dropzone);
            $userRequestCount[$user->getId()] = $nbAdressedRequests;
        }

        return $userRequestCount;
    }

    public function countDocsWithoutReceipt($drops)
    {
        $docWithoutReceiptCount = 0;
        foreach ($drops as $drop) {
            foreach ($drop->getDocuments() as $document) {
                $returnReceiptType = $this->receiptRepo->doneReturnReceiptForADocument($drop->getDropzone(), $document);
                // Initialisation de la variable car un document peut ne pas avoir d'accusé de réception.
                $id = 0;
                if (!empty($returnReceiptType)) {
                    // Récupération de la valeur de l'accusé de réceptoin
                    $id = $returnReceiptType[0]->getReturnReceiptType()->getId();
                    if ($id == 0) {
                        ++$docWithoutReceiptCount;
                    }
                } else {
                    ++$docWithoutReceiptCount;
                }
            }
        }

        return $docWithoutReceiptCount;
    }

    public function getTeacherComments($drops, $workspace)
    {
        $teacherDocComments = array();

        foreach ($drops as $drop) {
            foreach ($drop->getDocuments() as $document) {
                $userComments = $this->commentRepo->teacherCommentDocArray($document);
                $foundAdminComment = false;
                for ($i = 0; $i < count($userComments); ++$i) {
                    $user = $userComments[$i]->getUser();
                    $roles = $this->roleManager->getWorkspaceRolesForUser($user, $workspace);
                    for ($j = 0; $j < count($roles); ++$j) {
                        $roleName = $roles[$j]->getName();
                        if (strpos('_'.$roleName, 'ROLE_WS_MANAGER') === 1 && !$foundAdminComment) {
                            $teacherDocComments[$document->getId()] = $user->getFirstName().' '.$user->getLastName();
                            $foundAdminComment = true;
                        }
                    }
                }
            }
        }

        return $teacherDocComments;
    }
}
