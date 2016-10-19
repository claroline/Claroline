<?php

namespace Innova\CollecticielBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Innova\CollecticielBundle\Entity\Correction;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogCorrectionUpdateEvent;
use Innova\CollecticielBundle\Event\Log\LogDropReportEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneReturnReceiptEvent;
use Innova\CollecticielBundle\Form\CorrectionReportType;
use Innova\CollecticielBundle\Form\DocumentType;
use Innova\CollecticielBundle\Form\DropType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DropController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/drop/{resourceId}/user/{userId}", name="innova_collecticiel_drop_switch_admin", requirements={"resourceId" = "\d+", "userId" = "\d+"}
     * )
     * @Route(
     *      "/{resourceId}/drop/user/{userId}", name="innova_collecticiel_drop_switch", requirements={"resourceId" = "\d+", "userId" = "\d+"}
     * )
     * @Route(
     *      "/{resourceId}/drop", name="innova_collecticiel_drop", requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", isOptional="true", class="ClarolineCoreBundle:User",options={"id" = "userId"})
     * @Template()
     */
    public function dropAction(Dropzone $dropzone, User $user = null)
    {
        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');
        $dropzoneVoter->isAllowToOpen($dropzone);

        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $dropManager = $this->get('innova.manager.drop_manager');
        $translator = $this->get('translator');
        $flashbag = $this->getRequest()->getSession()->getFlashBag();

        if (!$user) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }

        if ($dropManager->isDropFinished($dropzone, $user)) {
            $flashbag->add('error', $translator->trans('You ve already made ​​your copy for this review', [], 'innova_collecticiel'));
            $url = $this->generateUrl('innova_collecticiel_open', ['resourceId' => $dropzone->getId()]);

            return $this->redirect($url);
        }

        $drop = $dropManager->getDrop($dropzone, $user);
        $form = $this->createForm(new DropType(), $drop);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if (count($drop->getDocuments()) === 0) {
                $form->addError(new FormError('Add at least one document'));
            }
            if ($form->isValid()) {
                $dropManager->saveDrop($drop);
                $flashbag->add('success', $translator->trans('Your copy has been saved', [], 'innova_collecticiel'));
                $url = $this->generateUrl('innova_collecticiel_open', ['resourceId' => $dropzone->getId()]);

                return $this->redirect($url);
            }
        }

        $recordOrTransmitNotations = [];

        foreach ($dropzone->getDrops() as $dropList) {
            $recordOrTransmitNotations += $dropManager->getRecordOrTransmitNotation($dropList);
        }

        $form_url = $this->createForm(new DocumentType(), null, ['documentType' => 'url']);
        $form_file = $this->createForm(new DocumentType(), null, ['documentType' => 'file']);
        $form_resource = $this->createForm(new DocumentType(), null, ['documentType' => 'resource']);
        $form_text = $this->createForm(new DocumentType(), null, ['documentType' => 'text']);

        $returnReceipts = $dropManager->getReturnReceipts($drop);
        $teacherComments = $dropManager->getDropTeacherComments($drop);
        $allowedTypes = $dropzoneManager->getAllowedTypes($dropzone);
        $progress = $dropzoneManager->getDropzoneProgressByUser($dropzone, $user);
        $canEdit = $dropzoneVoter->checkEditRight($dropzone);
        $activeRoute = $this->getRequest()->attributes->get('_route');
        $isOpen = $dropzoneManager->collecticielOpenOrNot($dropzone);

        $notationDocuments = $dropManager->getNotationForDocuments($drop);
        $notationCommentDocuments = $dropManager->getNotationCommentForDocuments($drop);
        $notationQualityDocuments = $dropManager->getNotationQualityForDocuments($drop);
        $notationAssessorDocuments = $dropManager->getNotationAssessorForDocuments($drop);
        $notationAppreciationDocuments = $dropManager->getAppreciationForDocuments($drop);

        $em = $this->getDoctrine()->getManager();
        $criteriaRepo = $em->getRepository('InnovaCollecticielBundle:GradingCriteria');
        $criteriasArray = $criteriaRepo->getCriteriaArrayForDropzone($dropzone);

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'form' => $form->createView(),
            'form_url' => $form_url->createView(),
            'form_file' => $form_file->createView(),
            'form_resource' => $form_resource->createView(),
            'form_text' => $form_text->createView(),
            'allowedTypes' => $allowedTypes,
            'dropzoneProgress' => $progress,
            'adminInnova' => $canEdit,
            'userNbTextToRead' => [],
            'activeRoute' => $activeRoute,
            'collecticielOpenOrNot' => $isOpen,
            'returnReceiptArray' => $returnReceipts,
            'teacherCommentDocArray' => $teacherComments,
            'notationsArray' => [],
            'notationDocumentsArray' => $notationDocuments,
            'notationCommentDocumentsArray' => $notationCommentDocuments,
            'notationQualityDocumentsArray' => $notationQualityDocuments,
            'notationAssessorDocumentsArray' => $notationAssessorDocuments,
            'recordOrTransmitNotationsArray' => $recordOrTransmitNotations,
            'notationAppreciationDocumentsArray' => $notationAppreciationDocuments,
            'criteriasArray' => $criteriasArray,
        ];
    }

    private function addDropsStats(Dropzone $dropzone, $array)
    {
        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');
        $array['nbDropCorrected'] = $dropRepo->countDropsFullyCorrected($dropzone);
        $array['nbDrop'] = $dropRepo->countDrops($dropzone);

        return $array;
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/by/user",
     *      name="innova_collecticiel_drops_by_user",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/user/{page}",
     *      name="innova_collecticiel_drops_by_user_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByUserAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByUserQuery($dropzone);

        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());
        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drops_by_user_paginated',
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'unterminated_drops' => $countUnterminatedDrops,
            'dropzone' => $dropzone,
            'pager' => $pager,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops",
     *      name="innova_collecticiel_drops",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/default",
     *      name="innova_collecticiel_drops_by_default",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/default/{page}",
     *      name="innova_collecticiel_drops_by_default_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     **/
    public function dropsByDefaultAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByReportAndDropDateQuery($dropzone);

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drops_by_user_paginated',
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'unterminated_drops' => $countUnterminatedDrops,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/by/report",
     *      name="innova_collecticiel_drops_by_report",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/report/{page}",
     *      name="innova_collecticiel_drops_by_report_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByReportAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedReportedQuery($dropzone);

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drops_by_user_paginated',
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'unterminated_drops' => $countUnterminatedDrops,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/by/date",
     *      name="innova_collecticiel_drops_by_date",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/date/{page}",
     *      name="innova_collecticiel_drops_by_date_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByDateAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByDropDateQuery($dropzone);
        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drops_by_date_paginated',
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'unterminated_drops' => $countUnterminatedDrops,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/awaiting",
     *      name="innova_collecticiel_drops_awaiting",
     *      requirements={"resourceId" = "\d+"},
     *      options={"expose"=true},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/awaiting/{page}",
     *      name="innova_collecticiel_drops_awaiting_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsAwaitingAction(Dropzone $dropzone, $page = 1)
    {
        $translator = $this->get('translator');
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $dropManager = $this->get('innova.manager.drop_manager');
        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');

        $dropzoneVoter->isAllowToOpen($dropzone);
        $dropzoneVoter->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('InnovaCollecticielBundle:Drop');
        $commentRepo = $em->getRepository('InnovaCollecticielBundle:Comment');
        $documentRepo = $em->getRepository('InnovaCollecticielBundle:Document');
        $receiptRepo = $em->getRepository('InnovaCollecticielBundle:ReturnReceipt');
        $notationRepo = $em->getRepository('InnovaCollecticielBundle:Notation');

        $scaleRepo = $em->getRepository('InnovaCollecticielBundle:GradingScale');
        $scalesArray = $scaleRepo->getScaleArrayForDropzone($dropzone);

        $criteriaRepo = $em->getRepository('InnovaCollecticielBundle:GradingCriteria');
        $criteriasArray = $criteriaRepo->getCriteriaArrayForDropzone($dropzone);

        $notationRepo = $em->getRepository('InnovaCollecticielBundle:GradingNotation');
        $notationsArray = $notationRepo->getNotationArrayForDropzone($dropzone);

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $workspace = $dropzone->getResourceNode()->getWorkspace();

        // dropsQuery : finished à TRUE et unlocked_drop à FALSE
        $dropsQuery = $dropRepo->getDropsAwaitingCorrectionQuery($dropzone, 1);

        // Nombre d'AR pour CE dropzone / Repo : ReturnReceipt
        $countReturnReceiptForDropzone = $receiptRepo->countTextToRead($currentUser, $dropzone);
        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());

        // Déclaration du compteur de documents sans accusé de réception
        $alertNbDocumentWithoutReturnReceipt = 0;
        $totalValideAndNotAdminDocs = 0;
        $countReturnReceiptForUserAndDropzone = 0;

        // Déclarations des nouveaux tableaux, qui seront passés à la vue
        $userToCommentCount = [];
        $userNbTextToRead = [];
        $haveReturnReceiptOrNotArray = [];
        $haveCommentOrNotArray = [];
        $notationDocuments = [];
        $recordOrTransmitNotations = [];
        $notationCommentDocuments = [];
        $notationQualityDocuments = [];
        $notationAssessorDocuments = [];
        $notationAppreciationDocuments = [];

        foreach ($dropzone->getDrops() as $drop) {
            $notationDocuments += $dropManager->getNotationForDocuments($drop);
            $recordOrTransmitNotations += $dropManager->getRecordOrTransmitNotation($drop);

            // Nombre de commentaires non lus / Repo : Comment
            $nbCommentsPerUser = $commentRepo->countCommentNotRead($drop->getUser());
            // Nombre de demandes adressées / Repo : Document
            $nbTextToRead = $documentRepo->countTextToRead($drop->getUser(), $drop->getDropZone());
            // Nombre de demandes adressées / Repo : Document
            $countValideAndNotAdminDocs = $documentRepo->countValideAndNotAdminDocs($currentUser, $drop);
            // Nombre d'AR pour cet utilisateur et pour ce dropzone / Repo : ReturnReceiputtwment
            $haveReturnReceiptOrNot = $receiptRepo->haveReturnReceiptOrNot($currentUser, $drop->getDropZone());
            $totalValideAndNotAdminDocs = $totalValideAndNotAdminDocs + $countValideAndNotAdminDocs;
            // Nombre d'AR pour cet utilisateur et pour ce dropzone / Repo : ReturnReceipt
            $countReturnReceiptForUserAndDropzone = $receiptRepo->countTextToReadAll($currentUser, $drop->getDropZone());
            $countReturnReceiptForUserAndDropzone = $countReturnReceiptForUserAndDropzone - 1;

            for ($i = 0; $i <= $countReturnReceiptForUserAndDropzone; ++$i) {
                $documentId = $haveReturnReceiptOrNot[$i]->getDocument()->getId();
                $returnReceiptTypeId = $haveReturnReceiptOrNot[$i]->getReturnReceiptType()->getId();
                $haveReturnReceiptOrNotArray[$documentId] = $returnReceiptTypeId;
            }

            // Boucle pour calcul si le document X a un commentaire déposé par l'enseignant
            foreach ($drop->getDocuments() as $document2) {
                if ($document2->getValidate() === 1) {
                    $documentId = $document2->getId();
                    // Ajout pour savoir si le document a un commentaire lu par l'enseignant
                    $commentReadForATeacherOrNot = $commentRepo->commentReadForATeacherOrNot($currentUser, $documentId);
                    $commentReadForATeacherOrNot2 = $commentRepo->commentReadForATeacherOrNot2($currentUser, $documentId);
                    $commentReadForATeacherOrNot3 = $commentRepo->commentReadForATeacherOrNot3($currentUser, $documentId);
                    $haveCommentOrNotArray[$documentId] = $commentReadForATeacherOrNot + $commentReadForATeacherOrNot2 + $commentReadForATeacherOrNot3;
                }
            }

            // Affectations des résultats dans les tableaux
            $dropUserId = $drop->getUser()->getId();
            $userToCommentCount[$dropUserId] = $nbCommentsPerUser;
            $userNbTextToRead[$dropUserId] = $nbTextToRead;

            $notationCommentDocuments += $dropManager->getNotationCommentForDocuments($drop);
            $notationQualityDocuments += $dropManager->getNotationQualityForDocuments($drop);
            $notationAssessorDocuments += $dropManager->getNotationAssessorForDocuments($drop);
            $notationAppreciationDocuments += $dropManager->getAppreciationForDocuments($drop);
        }

        // Calcul du nombre de documents sans accusé de réception
        $alertNbDocumentWithoutReturnReceipt = $totalValideAndNotAdminDocs - $countReturnReceiptForDropzone;

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                $url = $this->generateUrl('innova_collecticiel_drops_awaiting_paginated',
                    [
                        'resourceId' => $dropzone->getId(),
                        'page' => $pager->getNbPages(),
                    ]
                );

                return $this->redirect($url);
            } else {
                throw new NotFoundHttpException();
            }
        }

        if (count($pager) === 0) {
            $this->getRequest()->getSession()->getFlashBag()->add('success', $translator->trans('No copy waiting for correction', [], 'innova_collecticiel'));
        }

        $isOpen = $dropzoneManager->collecticielOpenOrNot($dropzone);
        $isAdmin = $dropzoneVoter->checkEditRight($dropzone);
        $docWithoutReceiptCount = $dropManager->countDocsWithoutReceipt($pager->getcurrentPageResults());
        $teacherDocComments = $dropManager->getTeacherComments($pager->getcurrentPageResults(), $workspace);

        $dataToView = $this->addDropsStats($dropzone, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'unterminated_drops' => $countUnterminatedDrops,
            'pager' => $pager,
            'nbCommentNotRead' => $userToCommentCount,
            'userNbTextToRead' => $userNbTextToRead,
            'adminInnova' => $isAdmin,
            'collecticielOpenOrNot' => $isOpen,
            'haveReturnReceiptOrNotArray' => $haveReturnReceiptOrNotArray,
            'alertNbDocumentWithoutReturnReceipt' => $docWithoutReceiptCount,
            'haveCommentOrNotArray' => $haveCommentOrNotArray,
            'teacherCommentDocArray' => $teacherDocComments,
            'maximumNotation' => $dropzone->getMaximumNotation(),
            'notationDocuments' => $notationDocuments,
            'recordOrTransmitNotations' => $recordOrTransmitNotations,
            'notationCommentDocumentsArray' => $notationCommentDocuments,
            'notationQualityDocumentsArray' => $notationQualityDocuments,
            'notationAssessorDocumentsArray' => $notationAssessorDocuments,
            'notationAppreciationDocumentsArray' => $notationAppreciationDocuments,
            'scalesArray' => $scalesArray,
            'criteriasArray' => $criteriasArray,
            'notationsArray' => $notationsArray,
        ]);

        return $dataToView;
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/delete/{dropId}/{tab}/{page}",
     *      name="innova_collecticiel_drops_delete",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "tab" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDeleteAction(Dropzone $dropzone, Drop $drop, $tab, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(new DropType(), $drop);

        $previousPath = 'innova_collecticiel_drops_by_user_paginated';
        if ($tab === 1) {
            $previousPath = 'innova_collecticiel_drops_by_date_paginated';
        } elseif ($tab === 2) {
            $previousPath = 'innova_collecticiel_drops_awaiting_paginated';
        }

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($drop);
                $em->flush();

                $url = $this->generateUrl($previousPath, [
                          'resourceId' => $dropzone->getId(),
                          'page' => $page,
                          ]
                );

                return $this->redirect($url);
            }
        }

        $view = 'InnovaCollecticielBundle:Drop:dropsDelete.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'InnovaCollecticielBundle:Drop:dropsDeleteModal.html.twig';
        }

        return $this->render($view, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'form' => $form->createView(),
            'previousPath' => $previousPath,
            'tab' => $tab,
            'page' => $page,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/{dropId}",
     *      name="innova_collecticiel_drops_detail",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsDetailAction(Dropzone $dropzone, $dropId)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropResult = $this
            ->getDoctrine()
            ->getRepository('InnovaCollecticielBundle:Drop')
            ->getDropAndCorrectionsAndDocumentsAndUser($dropzone, $dropId);

        $drop = null;
        $return = $this->redirect(
            $this->generateUrl(
                'innova_collecticiel_drops_awaiting',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            ));

        if (count($dropResult) > 0) {
            $drop = $dropResult[0];
            $return = [
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
                'isAllowedToEdit' => true,
            ];
        }

        return $return;
    }

    /**
     * @Route(
     *      "/{resourceId}/drop/detail/{dropId}",
     *      name="innova_collecticiel_drop_detail_by_user",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropDetailAction(Dropzone $dropzone, Drop $drop)
    {
        // check  if the User is allowed to open the dropZone.
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        // getting the userId to check if the current drop owner match with the loggued user.
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);
        $isAllowedToEdit = $this->get('security.context')->isGranted('EDIT', $collection);

        // getting the data
        $dropSecure = $this->getDoctrine()
            ->getRepository('InnovaCollecticielBundle:Drop')
            ->getDropAndValidEndedCorrectionsAndDocumentsByUser($dropzone, $drop->getId(), $userId);

        // if there is no result ( user is not the owner, or the drop has not ended Corrections , show 404)
        if (count($dropSecure) === 0) {
            if ($drop->getUser()->getId() !== $userId) {
                throw new AccessDeniedException();
            }
        } else {
            $drop = $dropSecure[0];
        }

        $showCorrections = false;

        // if drop is complete and corrections needed were made  and dropzone.showCorrection is true.
        $user = $drop->getUser();
        $em = $this->getDoctrine()->getManager();
        $nbCorrections = $em
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countFinished($dropzone, $user);

        if ($dropzone->getDiplayCorrectionsToLearners()
        && $drop->countFinishedCorrections() >= $dropzone->getExpectedTotalCorrection()
        && $dropzone->getExpectedTotalCorrection() <= $nbCorrections
        || ($dropzone->isFinished()
        && $dropzone->getDiplayCorrectionsToLearners()
        || $drop->getUnlockedUser())
        ) {
            $showCorrections = true;
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'isAllowedToEdit' => $isAllowedToEdit,
            'showCorrections' => $showCorrections,
        ];
    }

    /**
     * @param Drop $drop
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route(
     *                                                            "/unlock/drop/{dropId}",
     *                                                            name="innova_collecticiel_unlock_drop",
     *                                                            requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     *                                                            )
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("user", options={
     *                                                            "authenticatedUser" = true,
     *                                                            "messageEnabled" = true,
     *                                                            "messageTranslationKey" = "This action requires authentication. Please login.",
     *                                                            "messageTranslationDomain" = "innova_collecticiel"
     *                                                            })
     * @Template()
     */
    public function unlockDropAction(Drop $drop, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $drop->setUnlockedDrop(true);
        $em->flush();

        $this->getRequest()
            ->getSession()
            ->getFlashBag()
            ->add('success', $this->get('translator')->trans('Drop have been unlocked', [], 'innova_collecticiel')
            );

        $dropzoneId = $drop->getDropzone()->getId();

        return $this->redirect(
            $this->generateUrl(
                'innova_collecticiel_drops_awaiting',
                [
                    'resourceId' => $dropzoneId,
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/report/drop/{correctionId}",
     *      name="innova_collecticiel_report_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("correction", class="InnovaCollecticielBundle:Correction", options={"id" = "correctionId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "innova_collecticiel"
     * })
     * @Template()
     */
    public function reportDropAction(Correction $correction, User $user)
    {
        $dropzone = $correction->getDropzone();
        $drop = $correction->getDrop();
        $em = $this->getDoctrine()->getManager();
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        try {
            $curent_user_correction = $em->getRepository('InnovaCollecticielBundle:Correction')->getNotFinished($dropzone, $user);
        } catch (NotFoundHttpException $e) {
            throw new AccessDeniedException();
        }

        if ($curent_user_correction === null || $curent_user_correction->getId() !== $correction->getId()) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(new CorrectionReportType(), $correction);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $drop->setReported(true);
                $correction->setReporter(true);
                $correction->setEndDate(new \DateTime());
                $correction->setFinished(true);
                $correction->setTotalGrade(0);

                $em->persist($drop);
                $em->persist($correction);
                $em->flush();

                $this->dispatchDropReportEvent($dropzone, $drop, $correction);
                $this->getRequest()->getSession()->getFlashBag()->add(
                  'success',
                  $this->get('translator')->trans('Your report has been saved', [], 'innova_collecticiel')
                );

                $url = $this->generateUrl('innova_collecticiel_open', ['resourceId' => $dropzone->getId()]);

                return $this->redirect($url);
            }
        }

        $view = 'InnovaCollecticielBundle:Drop:reportDrop.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'InnovaCollecticielBundle:Drop:reportDropModal.html.twig';
        }

        return $this->render($view, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'correction' => $correction,
            'form' => $form->createView(),
        ]);
    }

    protected function dispatchDropReportEvent(Dropzone $dropzone, Drop $drop, Correction $correction)
    {
        $rm = $this->get('claroline.manager.role_manager');
        $event = new LogDropReportEvent($dropzone, $drop, $correction, $rm);
        $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @Route(
     *      "/{resourceId}/remove/report/{dropId}/{correctionId}/{invalidate}",
     *      name="innova_collecticiel_remove_report",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+", "invalidate" = "0|1"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="InnovaCollecticielBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function removeReportAction(Dropzone $dropzone, Drop $drop, Correction $correction, $invalidate)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $correction->setReporter(false);

        if ($invalidate === 1) {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        $correctionRepo = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:Correction');
        if ($correctionRepo->countReporter($dropzone, $drop) === 0) {
            $drop->setReported(false);
            $em->persist($drop);
            $em->flush();
        }

        $event = new LogCorrectionUpdateEvent($dropzone, $drop, $correction);
        $this->dispatch($event);

        return $this->redirect(
            $this->generateUrl(
                'innova_collecticiel_drops_detail',
                [
                    'resourceId' => $dropzone->getId(),
                    'dropId' => $drop->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/autoclosedrops/confirm",
     *      name="innova_collecticiel_auto_close_drops_confirmation",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function autoCloseDropsConfirmationAction(Dropzone $dropzone)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $view = $this->getRequest()->isXmlHttpRequest()
          ? 'InnovaCollecticielBundle:Dropzone:confirmCloseUnterminatedDropModal.html.twig'
          : 'InnovaCollecticielBundle:Dropzone:confirmCloseUnterminatedDrop.html.twig';

        return $this->render($view, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/autoclosedrops",
     *      name="innova_collecticiel_auto_close_drops",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     */
    public function autoCloseDropsAction(Dropzone $dropzone)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $dropzoneManager->closeDropzoneOpenedDrops($dropzone, true);

        return $this->redirect(
            $this->generateUrl(
                'innova_collecticiel_drops_awaiting',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/shared/spaces",
     *      name="innova_collecticiel_shared_spaces",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/shared/spaces/{page}",
     *      name="innova_collecticiel_shared_spaces_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function sharedSpacesAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $dropManager = $this->get('innova.manager.drop_manager');
        $userManager = $this->get('claroline.manager.user_manager');
        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');

        $resourceNode = $dropzone->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $dropRepo = $this->getDoctrine()->getManager()->getRepository('InnovaCollecticielBundle:Drop');

        // dropsQuery : finished à TRUE et unlocked_drop à FALSE
        $dropsQuery = $dropRepo->getSharedSpacesQuery($dropzone, $workspace);
        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_shared_spaces_paginated',
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        $isAdmin = $dropzoneVoter->checkEditRight($dropzone);
        $isOpen = $dropzoneManager->collecticielOpenOrNot($dropzone);
        $userNbDocDropped = $dropManager->getDroppedDocsByUserCount($dropzone);
        $userNbAdressedRequests = $dropManager->getRequestByUserCount($dropzone);
        $countUnterminatedDrops = $dropRepo->countUnterminatedDropsByDropzone($dropzone->getId());
        $usersByWorkspaces = $userManager->getUsersByWorkspaces([$workspace->getId()], $page, 20, false);

        $dataToView = $this->addDropsStats($dropzone, [
            'workspace' => $workspace,
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'unterminated_drops' => $countUnterminatedDrops,
            'pager' => $pager,
            'userNbDocDropped' => $userNbDocDropped,
            'userNbAdressedRequests' => $userNbAdressedRequests,
            'adminInnova' => $isAdmin,
            'usersByWorkspaces' => $usersByWorkspaces,
            'collecticielOpenOrNot' => $isOpen,
        ]);

        return $dataToView;
    }

    /**
     * @Route(
     *      "/drop/reception",
     *      name="innova_collecticiel_return_receipt",
     *      options={"expose"=true}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function returnReceiptAction()
    {
        // Récupération de l'ID de l'accusé de réception choisi
        $returnReceiptId = $this->get('request')->query->get('returnReceiptId');
        $em = $this->getDoctrine()->getManager();
        $receiptRepo = $em->getRepository('InnovaCollecticielBundle:ReturnReceipt');
        $receiptManager = $this->get('innova.manager.returnreceipt_manager');

        // Récupération de l'ID du dropzone choisi
        $dropzoneId = $this->get('request')->query->get('dropzoneId');
        $dropzone = $em->getRepository('InnovaCollecticielBundle:Dropzone')->find($dropzoneId);
        // Récupération des documents sélectionnés
        $arrayDocsId = $this->get('request')->query->get('arrayDocsId');
        // Récupération de l'utilisateur
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $returnReceiptType = $em->getRepository('InnovaCollecticielBundle:ReturnReceiptType')->find($returnReceiptId);

        // Parcours des documents sélectionnés et insertion en base de données
        foreach ($arrayDocsId as $documentId) {
            // Par le JS, le document est transmis sous la forme "document_id_XX"
            $docIdS = explode('_', $documentId);
            $docId = $docIdS[2];

            if ($docId > 0) {
                $document = $em->getRepository('InnovaCollecticielBundle:Document')->find($docId);
                // Nombre de demandes adressées/ Repo : Document
                $countReceipts = $receiptRepo->haveReturnReceiptOrNotForADocument($user, $dropzone, $document);
                // S'il y a déjà un accusé de réception alors je le supprime avant de créer le nouveau
                if ($countReceipts !== 0) {
                    $receiptRepo->deleteReturnReceipt($user, $dropzone, $document);
                }
                // Création du nouvel accusé de réception
                $receiptManager->create($document, $user, $dropzone, $returnReceiptType);
                // Ici, on récupère celui qui vient de déposer le nouveau document
                $userDropDocument = $document->getDrop()->getUser()->getId();
                $event = new LogDropzoneReturnReceiptEvent($document, $dropzone, [$userDropDocument]);

                $this->get('event_dispatcher')->dispatch('log', $event);
            }
        }
        $em->flush();

        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $url = $this->generateUrl('innova_collecticiel_drops_awaiting', ['resourceId' => $dropzoneId]);

        return new JsonResponse(['link' => $url]);
    }

    /**
     * @Route(
     *      "/back/link",
     *      name="innova_collecticiel_back_link",
     *      options={"expose"=true}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function backLinkAction()
    {
        $dropzoneId = $this->get('request')->query->get('dropzoneId');
        $url = $this->generateUrl('innova_collecticiel_drops_awaiting', ['resourceId' => $dropzoneId]);

        return new JsonResponse(['link' => $url]);
    }
}
