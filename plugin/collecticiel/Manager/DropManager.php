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
    private $notationRepo;

    /**
     * @DI\InjectParams(
     * {
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
        $this->notationRepo = $this->em->getRepository('InnovaCollecticielBundle:Notation');
    }

    /**
     * Handle Create Drop.
     *
     * @param Dropzone $dropzone dropzone
     * @param User     $user     user
     *
     * @return drop
     */
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

    /**
     * Handle Save Drop.
     *
     * @param Drop $drop drop
     *
     * @return none
     */
    public function saveDrop(Drop $drop)
    {
        $em->persist($drop);
        $em->flush();

        $this->eventDispatcher->dispatch('log', new LogDropEndEvent($drop->getDropzone(), $drop, $this->roleManager));
    }

    /**
     * Handle if Drop is finished or not.
     *
     * @param Dropzone $dropzone dropzone
     * @param User     $user     user
     *
     * @return isFinished
     */
    public function isDropFinished(Dropzone $dropzone, User $user)
    {
        return $isFinished = $this->dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => true)) !== null
            ? true
            : false;
    }

    /**
     * Get Drop For Dropzone/User.
     *
     * @param Dropzone $dropzone dropzone
     * @param User     $user     user
     *
     * @return drop
     */
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
                if ($document->getValidate()) {
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

    /**
     * Handle Notation for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationDocuments
     */
    public function getNotationForDocuments(Drop $drop)
    {
        $notationDocuments = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationDocuments[$documentId] = 0; // Pas de notation donc = 0
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    $notationDocuments[$documentId] = $notation->getNote(); // Valeur de la notation
                }
            }
        }

        return $notationDocuments;
    }

    /**
     * Handle CommentText for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationCommentTextDocuments
     */
    public function getNotationCommentForDocuments(Drop $drop)
    {
        $notationCommentTextDocuments = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationCommentTextDocuments[$documentId] = '';
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    if (strlen($notation->getCommentText()) == 0) {
                        $notationCommentTextDocuments[$documentId] = '';
                    } else {
                        $notationCommentTextDocuments[$documentId]
                            = $notation->getCommentText();
                    }
                }
            }
        }

        return $notationCommentTextDocuments;
    }

    /**
     * Handle QualityText for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationQualityDocuments
     */
    public function getNotationQualityForDocuments(Drop $drop)
    {
        $notationQualityDocuments = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationQualityDocuments[$documentId] = '';
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    if (strlen($notation->getQualityText()) == 0) {
                        $notationQualityDocuments[$documentId] = '';
                    } else {
                        $notationQualityDocuments[$documentId]
                            = $notation->getQualityText();
                    }
                }
            }
        }

        return $notationQualityDocuments;
    }

    /**
     * Handle Appreciation for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationAppreciation
     */
    public function getAppreciationForDocuments(Drop $drop)
    {
        $notationAppreciation = array();

        $dropzone = $drop->getDropzone();
        $this->gradingScaleRepo = $this->em->getRepository('InnovaCollecticielBundle:GradingScale');

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationAppreciation[$documentId] = 99;
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    if ($notation->getAppreciation() > 0) {
                        $gradingScale = $this->gradingScaleRepo->find($notation->getAppreciation());
                        $notationAppreciation[$documentId] = $gradingScale->getScaleName();
                    } else {
                        $notationAppreciation[$documentId] = 0;
                    }
                }
            }
        }

        return $notationAppreciation;
    }

    /**
     * Handle QualityText for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationQualityDocuments
     */
    public function getNotationAssessorForDocuments(Drop $drop)
    {
        $notationAssessorDocuments = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationAssessorDocuments[$documentId] = '';
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    $notationAssessorDocuments[$documentId] = $notation->getUser();
                }
            }
        }

        return $notationAssessorDocuments;
    }

    /**
     * Handle Record or Transnit Notation for Documents.
     *
     * @param Drop $drop drop
     *
     * @return recordOrTransmitNotationArray
     */
    public function getRecordOrTransmitNotation(Drop $drop)
    {
        $recordOrTransmitNotationArray = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir si la notation a été transmise ou pas.
            $recordOrTransmitNotations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            $countRecordOrTransmitNotation = count($recordOrTransmitNotations);

            if ($countRecordOrTransmitNotation == 0) {
                $recordOrTransmitNotationArray[$documentId] = 99;
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($recordOrTransmitNotations as $recordOrTransmitNotation) {
                    if ($recordOrTransmitNotation->getRecordOrTransmit()) {
                        $recordOrTransmitNotationArray[$documentId] = 1;
                    } else {
                        $recordOrTransmitNotationArray[$documentId] = 0;
                    }
                }
            }
        }

        return $recordOrTransmitNotationArray;
    }

    /**
     * Handle CommentText for Documents.
     *
     * @param Drop $drop drop
     *
     * @return notationCommentTextDocuments
     */
    public function getChoiceTextForDocuments(Drop $drop)
    {
        $notationChoiceTextDocuments = array();

        $dropzone = $drop->getDropzone();

        foreach ($drop->getDocuments() as $document) {
            $documentId = $document->getId();

            // Ajout pour avoir la notation.
            $notations = $this
                ->em->getRepository('InnovaCollecticielBundle:Notation')
                ->findBy(
                    array(
                        'document' => $documentId,
                        'dropzone' => $dropzone->getId(),
                    )
                );

            // Nombre de notation pour le document et pour le dropzone
            $countExistNotation = count($notations);

            if ($countExistNotation == 0) {
                $notationCommentTextDocuments[$documentId] = '';
            } else {
                // Parcours des commentaires des documents sélectionnés
                foreach ($notations as $notation) {
                    if (strlen($notation->getCommentText()) == 0) {
                        $notationCommentTextDocuments[$documentId] = '';
                    } else {
                        $notationCommentTextDocuments[$documentId]
                            = $notation->getCommentText();
                    }
                }
            }
        }

        return $notationChoiceTextDocuments;
    }
}
