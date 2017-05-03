<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEndEvent;
use Icap\DropzoneBundle\Event\Log\LogDropReportEvent;
use Icap\DropzoneBundle\Event\Log\LogDropStartEvent;
use Icap\DropzoneBundle\Form\CorrectionReportType;
use Icap\DropzoneBundle\Form\DocumentType;
use Icap\DropzoneBundle\Form\DropType;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DropController extends DropzoneBaseController
{
    private $eventDispatcher;
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(StrictDispatcher $eventDispatcher, TokenStorageInterface $tokenStorage)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route(
     *      "/{resourceId}/drop",
     *      name="icap_dropzone_drop",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function dropAction(Request $request, Dropzone $dropzone, $user)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');

        if ($dropRepo->findOneBy(['dropzone' => $dropzone, 'user' => $user, 'finished' => true]) !== null) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('You ve already made your copy for this review', [], 'icap_dropzone')
            );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    [
                        'resourceId' => $dropzone->getId(),
                    ]
                )
            );
        }

        $notFinishedDrop = $dropRepo->findOneBy(['dropzone' => $dropzone, 'user' => $user, 'finished' => false]);
        if ($notFinishedDrop === null) {
            $notFinishedDrop = new Drop();
            $number = ($dropRepo->getLastNumber($dropzone) + 1);
            $notFinishedDrop->setNumber($number);

            $notFinishedDrop->setUser($user);
            $notFinishedDrop->setDropzone($dropzone);
            $notFinishedDrop->setFinished(false);

            $em->persist($notFinishedDrop);
            $em->flush();
            $em->refresh($notFinishedDrop);

            $event = new LogDropStartEvent($dropzone, $notFinishedDrop);
            $this->dispatch($event);
        }

        $form = $this->createForm(new DropType(), $notFinishedDrop);
        $form_url = $this->createForm(new DocumentType(), null, ['documentType' => 'url']);
        $form_file = $this->createForm(new DocumentType(), null, ['documentType' => 'file']);
        $form_resource = $this->createForm(new DocumentType(), null, ['documentType' => 'resource']);
        $form_text = $this->createForm(new DocumentType(), null, ['documentType' => 'text']);
        $drop = $notFinishedDrop;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (count($notFinishedDrop->getDocuments()) === 0) {
                $form->addError(new FormError('Add at least one document'));
            }

            if ($form->isValid()) {
                if ($notFinishedDrop->getHiddenDirectory()) {
                    // change the folder name to take the datetime of the drop event
                    $dropDate = new \DateTime();
                    $date_format = $this->get('translator')->trans('date_form_datepicker_php', [], 'platform');
                    $rm = $this->get('claroline.manager.resource_manager');
                    $node = $rm->getById($notFinishedDrop->getHiddenDirectory()->getId());
                    // set the date time of the drop.
                    $folderName = $node->getName();
                    $rm->rename($node, $folderName.' '.$dropDate->format($date_format.' '.'H:i:s')); //());
                }

                $notFinishedDrop->setFinished(true);

                $em = $this->getDoctrine()->getManager();
                $em->persist($notFinishedDrop);

                $em->flush();

                $rm = $this->get('claroline.manager.role_manager');
                $event = new LogDropEndEvent($dropzone, $notFinishedDrop, $rm);
                $this->dispatch($event);

                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Your copy has been saved', [], 'icap_dropzone')
                );

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_open',
                        [
                            'resourceId' => $dropzone->getId(),
                        ]
                    )
                );
            }
        }

        $allowedTypes = [];
        if ($dropzone->getAllowWorkspaceResource()) {
            $allowedTypes[] = 'resource';
        }
        if ($dropzone->getAllowUpload()) {
            $allowedTypes[] = 'file';
        }
        if ($dropzone->getAllowUrl()) {
            $allowedTypes[] = 'url';
        }
        if ($dropzone->getAllowRichText()) {
            $allowedTypes[] = 'text';
        }

        $resourceTypes = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        $dropzoneManager = $this->get('icap.manager.dropzone_manager');
        $dropzoneProgress = $dropzoneManager->getDropzoneProgressByUser($dropzone, $user);

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
            'resourceTypes' => $resourceTypes,
            'dropzoneProgress' => $dropzoneProgress,
        ];
    }

    private function addDropsStats($dropzone, $array)
    {
        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $array['nbDropCorrected'] = $dropRepo->countDropsFullyCorrected($dropzone);
        $array['nbDrop'] = $dropRepo->countDrops($dropzone);

        return $array;
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/by/user",
     *      name="icap_dropzone_drops_by_user",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/user/{page}",
     *      name="icap_dropzone_drops_by_user_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByUserAction($dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
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
                        'icap_dropzone_drops_by_user_paginated',
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
     *      "/{resourceId}/unlock/{userId}",
     *      name="icap_dropzone_unlock_user",
     *      requirements={"resourceId" = "\d+", "userId" = "\d+"}
     * )
     * @ParamConverter("dropzone",class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     *
     * @param \Icap\DropzoneBundle\Entity\Dropzone $dropzone
     * @param $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @internal param $user
     * @internal param $userId
     */
    public function unlockUser(Dropzone $dropzone, $userId)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);
        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $drop = $dropRepo->getDropByUser($dropzone->getId(), $userId);
        if ($drop !== null) {
            $drop->setUnlockedUser(true);
        }
        $em = $this->getDoctrine()->getManager();
        $em->merge($drop);
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_examiners',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/unlock/all",
     *      name="icap_dropzone_unlock_all_user",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone",class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     *
     * @param \Icap\DropzoneBundle\Entity\Dropzone $dropzone
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @internal param $user
     * @internal param $userId
     */
    public function unlockUsers(Dropzone $dropzone)
    {
        return $this->unlockOrLockUsers($dropzone, true);
    }

    /**
     * @Route(
     *      "/{resourceId}/unlock/cancel",
     *      name="icap_dropzone_unlock_cancel",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone",class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     *
     * @param \Icap\DropzoneBundle\Entity\Dropzone $dropzone
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @internal param $user
     * @internal param $userId
     */
    public function unlockUsersCancel(Dropzone $dropzone)
    {
        return $this->unlockOrLockUsers($dropzone, false);
    }

    /**
     *  Factorised function for lock & unlock users in a dropzone.
     *
     * @param Dropzone $dropzone
     * @param bool     $unlock
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function unlockOrLockUsers(Dropzone $dropzone, $unlock = true)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $drops = $dropRepo->findBy(['dropzone' => $dropzone->getId(), 'unlockedUser' => !$unlock]);

        foreach ($drops as $drop) {
            $drop->setUnlockedUser($unlock);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_examiners',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops",
     *      name="icap_dropzone_drops",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/default",
     *      name="icap_dropzone_drops_by_default",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/default/{page}",
     *      name="icap_dropzone_drops_by_default_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     **/
    public function dropsByDefaultAction($dropzone, $page = 1)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
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
                        'icap_dropzone_drops_by_user_paginated',
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
     *      name="icap_dropzone_drops_by_report",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/report/{page}",
     *      name="icap_dropzone_drops_by_report_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByReportAction($dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
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
                        'icap_dropzone_drops_by_user_paginated',
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
     *      name="icap_dropzone_drops_by_date",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/date/{page}",
     *      name="icap_dropzone_drops_by_date_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByDateAction($dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
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
                        'icap_dropzone_drops_by_date_paginated',
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
     *      name="icap_dropzone_drops_awaiting",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/awaiting/{page}",
     *      name="icap_dropzone_drops_awaiting_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsAwaitingAction($dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsAwaitingCorrectionQuery($dropzone);

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
                        'icap_dropzone_drops_awaiting_paginated',
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
            'unterminated_drops' => $countUnterminatedDrops,
            'pager' => $pager,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/unfinished",
     *      name="icap_dropzone_drops_unfinished",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/unfinished/{page}",
     *      name="icap_dropzone_drops_unfinished_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsUnfinishedAction($dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $dropsQuery = $dropRepo->getUnfinishedDropsQuery($dropzone);

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
                        'icap_dropzone_drops_unfinished_paginated',
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
            'unterminated_drops' => $countUnterminatedDrops,
            'pager' => $pager,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/delete/{dropId}/{tab}/{page}",
     *      name="icap_dropzone_drops_delete",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "tab" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDeleteAction(Request $request, $dropzone, $drop, $tab, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(new DropType(), $drop);

        $previousPath = 'icap_dropzone_drops_by_user_paginated';
        if ($tab === 1) {
            $previousPath = 'icap_dropzone_drops_by_date_paginated';
        } elseif ($tab === 2) {
            $previousPath = 'icap_dropzone_drops_awaiting_paginated';
        }

        if ($request->isMethod('POST')) {
            //This is commented as a temporary fix. The form is always invalid for some reason on our servers...
            //It can be uncommented once a true fix is found.
            //$form->handleRequest($request);
            //if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
            $em->remove($drop);
            $em->flush();

            return $this->redirect(
                    $this->generateUrl(
                        $previousPath,
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $page,
                        ]
                    )

                );
            //}
        }

        $view = 'IcapDropzoneBundle:Drop:dropsDelete.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Drop:dropsDeleteModal.html.twig';
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
     *      name="icap_dropzone_drops_detail",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsDetailAction($dropzone, $dropId)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropResult = $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Drop')
            ->getDropAndCorrectionsAndDocumentsAndUser($dropzone, $dropId);

        $drop = null;
        $return = $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_awaiting',
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
     *      name="icap_dropzone_drop_detail_by_user",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropDetailAction(Dropzone $dropzone, Drop $drop)
    {
        // check  if the User is allowed to open the dropZone.
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        // getting the userId to check if the current drop owner match with the loggued user.
        $userId = $this->tokenStorage->getToken()->getUser()->getId();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);
        $isAllowedToEdit = $this->get('security.authorization_checker')->isGranted('EDIT', $collection);

        // getting the data
        $dropSecure = $this->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Drop')
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
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countFinished($dropzone, $user);

        if ($dropzone->getDiplayCorrectionsToLearners() && $drop->countFinishedCorrections() >= $dropzone->getExpectedTotalCorrection() &&
            $dropzone->getExpectedTotalCorrection() <= $nbCorrections || ($dropzone->isFinished() && $dropzone->getDiplayCorrectionsToLearners() || $drop->getUnlockedUser())
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
     *      "/unlock/drop/{dropId}",
     *      name="icap_dropzone_unlock_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "This action requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function unlockDropAction(Request $request, Drop $drop, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $drop->setUnlockedDrop(true);
        $em->flush();

        $request
            ->getSession()
            ->getFlashBag()
            ->add('success', $this->get('translator')->trans('Drop have been unlocked', [], 'icap_dropzone')
            );

        $dropzoneId = $drop->getDropzone()->getId();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_awaiting',
                [
                    'resourceId' => $dropzoneId,
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/report/drop/{correctionId}",
     *      name="icap_dropzone_report_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function reportDropAction(Request $request, Correction $correction, User $user)
    {
        $dropzone = $correction->getDropzone();
        $drop = $correction->getDrop();
        $em = $this->getDoctrine()->getManager();
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        try {
            $curent_user_correction = $em->getRepository('IcapDropzoneBundle:Correction')->getNotFinished($dropzone, $user);
        } catch (NotFoundHttpException $e) {
            throw new AccessDeniedException();
        }

        if ($curent_user_correction === null || $curent_user_correction->getId() !== $correction->getId()) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(new CorrectionReportType(), $correction);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
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
                $this
                    ->getRequest()
                    ->getSession()
                    ->getFlashBag()
                    ->add('success', $this->get('translator')->trans('Your report has been saved', [], 'icap_dropzone'));

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_open',
                        [
                            'resourceId' => $dropzone->getId(),
                        ]
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Drop:reportDrop.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Drop:reportDropModal.html.twig';
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
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+", "invalidate" = "0|1"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function removeReportAction(Dropzone $dropzone, Drop $drop, Correction $correction, $invalidate)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $correction->setReporter(false);

        if ($invalidate === 1) {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        $correctionRepo = $this->getDoctrine()->getRepository('IcapDropzoneBundle:Correction');
        if ($correctionRepo->countReporter($dropzone, $drop) === 0) {
            $drop->setReported(false);
            $em->persist($drop);
            $em->flush();
        }

        $event = new LogCorrectionUpdateEvent($dropzone, $drop, $correction);
        $this->dispatch($event);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
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
     *      name="icap_dropzone_auto_close_drops_confirmation",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function autoCloseDropsConfirmationAction(Request $request, $dropzone)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $view = 'IcapDropzoneBundle:Dropzone:confirmCloseUnterminatedDrop.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Dropzone:confirmCloseUnterminatedDropModal.html.twig';
        }

        return $this->render($view, [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/autoclosedrops",
     *      name="icap_dropzone_auto_close_drops",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     */
    public function autoCloseDropsAction($dropzone)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $dropzoneManager = $this->get('icap.manager.dropzone_manager');
        $dropzoneManager->closeDropzoneOpenedDrops($dropzone, true);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_awaiting',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/close/{dropId}",
     *      name="icap_dropzone_close_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     */
    public function closeDropAction(Request $request, $dropzone, Drop $drop)
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('IcapDropzoneBundle:Drop:dropsCloseModal.html.twig', [
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
            ]);
        }

        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);
        $em = $this->getDoctrine()->getManager();
        $drop->setFinished(true);
        $em->flush();

        $request->getSession()->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('copy closed', [], 'icap_dropzone')
        );

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_unfinished',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/remind/{dropId}/user/{userId}",
     *      name="icap_dropzone_remind_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "userId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"id" = "userId"})
     */
    public function sendReminderAction(Request $request, $dropzone, $drop, $user)
    {
        $this->eventDispatcher->dispatch(
            'claroline_message_sending_to_users',
            'SendMessage',
            [
                $this->tokenStorage->getToken()->getUser(),
                $this->get('translator')->trans('reminder message', [
                    '%dropzonename%' => $dropzone->getResourceNode()->getName(),
                    '%dropdate%' => $drop->getDropDate()->format($this->get('translator')->trans('date_format_php', [], 'icap_dropzone')),
                    '%confirmation_url%' => $this->generateUrl(
                        'icap_dropzone_drop',
                        [
                            'resourceId' => $dropzone->getId(),
                        ]
                    ),
                ], 'icap_dropzone'),
                $this->get('translator')->trans('reminder object', [], 'icap_dropzone'),
                null,
                [$user],
                true,
            ]
        );

        $request->getSession()->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('reminder sent', [], 'icap_dropzone')
        );

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_unfinished',
                [
                    'resourceId' => $dropzone->getId(),
                ]
            )
        );
    }
}
