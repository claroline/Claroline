<?php

namespace FormaLibre\PresenceBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Manager\CursusManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\PresenceRights;
use FormaLibre\PresenceBundle\Entity\Releves;
use FormaLibre\PresenceBundle\Entity\SchoolYear;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Form\Type\CollReleveType;
use FormaLibre\PresenceBundle\Manager\PresenceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PresenceController extends Controller
{
    private $om;
    private $em;
    private $presenceRepo;
    private $periodRepo;
    private $groupRepo;
    private $userRepo;
    private $schoolYearRepo;
    private $router;
    private $presenceManager;
    private $cursusManager;

    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "router"             = @DI\Inject("router"),
     *      "presenceManager"    = @DI\Inject("formalibre.manager.presence_manager"),
     *      "cursusManager"      = @DI\Inject("claroline.manager.cursus_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        EntityManager $em,
        RouterInterface $router,
        PresenceManager $presenceManager,
        CursusManager $cursusManager
      ) {
        $this->router = $router;
        $this->om = $om;
        $this->em = $em;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->periodRepo = $om->getRepository('FormaLibrePresenceBundle:Period');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->statuRepo = $om->getRepository('FormaLibrePresenceBundle:Status');
        $this->presenceRepo = $om->getRepository('FormaLibrePresenceBundle:Presence');
        $this->schoolYearRepo = $om->getRepository('FormaLibrePresenceBundle:SchoolYear');
        $this->presenceManager = $presenceManager;
        $this->cursusManager = $cursusManager;
    }

    /**
     * @EXT\Route(
     *     "/presence/tool/index",
     *     name="formalibre_presence_tool_index",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function ToolIndexAction(User $user)
    {
        $SchoolYear = $this->schoolYearRepo->findOneBySchoolYearActual(true);
        $Presences = $this->presenceRepo->findAll();
        $Periods = $this->periodRepo->findByVisibility(true);
        $canViewPersonalArchives = $this->presenceManager->checkRights($user, PresenceRights::PERSONAL_ARCHIVES);
        $canCkeckPresences = $this->presenceManager->checkRights($user, PresenceRights::CHECK_PRESENCES);
        $canViewArchives = $this->presenceManager->checkRights($user, PresenceRights::READING_ARCHIVES);

        if (!is_null($SchoolYear)) {
            $SchoolYearBeginHour = $SchoolYear->getSchoolDayBeginHour();
            $SchoolYearEndHour = $SchoolYear->getSchoolDayEndHour();
        } else {
            $SchoolYearBeginHour = '08:00:00';
            $SchoolYearEndHour = '18:00:00';
        }

        return ['user' => $user,
                     'presences' => $Presences,
                     'periods' => $Periods,
                     'canViewPersonalArchives' => $canViewPersonalArchives,
                     'canCheckPresences' => $canCkeckPresences,
                     'canViewArchives' => $canViewArchives,
                     'schoolYearBeginHour' => $SchoolYearBeginHour,
                     'schoolYearEndHour' => $SchoolYearEndHour, ];
    }

    /**
     * @EXT\Route(
     *     "/presence/choix_classe/period/{period}/date/{date}",
     *     name="formalibre_choix_classe",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function ChoixClasseAction(User $user, Period $period, Request $request, $date)
    {
        $sessionsByUser = $this->cursusManager->getSessionsByUserAndType($user, 1);
        $form = $this->createFormBuilder()

            ->add('selection', 'entity', [
                'label' => 'Classe:',
                'class' => 'Claroline\CursusBundle\Entity\CourseSession',
                'choices' => $sessionsByUser,
                'property' => 'getShortNameWithCourse',
                'empty_value' => 'Choisissez un groupe', ])
            ->add('valider', 'submit', [
                'label' => 'Relever les présences', ])
            ->getForm();

        $request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            $session = $form->get('selection')->getData();

            return $this->redirect($this->generateUrl('formalibre_presence_releve', ['period' => $period->getId(),
                                                                                              'date' => $date,
                                                                                              'session' => $session->getId(), ]));
        }

        return ['form' => $form->createView(),
                         'user' => $user,
                         'period' => $period,
                         'date' => $date, ];
    }

    /**
     * @EXT\Route(
     *     "/presence/releve/period/{period}/date/{date}/session/{session}",
     *     name="formalibre_presence_releve",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceReleveAction(Request $request, User $user, Period $period, $date, CourseSession $session)
    {
        $canCkeckPresences = $this->presenceManager->checkRights($user, PresenceRights::CHECK_PRESENCES);

        $dateFormat = \DateTime::createFromFormat('d-m-y', $date);
        $dateFormat->setTime(0, 0);

        $Presences = $this->presenceRepo->OrderByStudent($session, $dateFormat, $period);
        $dayPresences = $this->presenceRepo->OrderByNumPeriod($session, $dateFormat);

        $Groups = $this->groupRepo->findAll();

        $Users = $this->cursusManager->getUsersBySessionAndType($session, 0);

        $Null = $this->statuRepo->findOneByStatusName('');
        $liststatus = $this->statuRepo->findByStatusByDefault(false);

        if (!$Presences) {
            $Presences = [];
            foreach ($Users as $student) {
                $actualPresence = new Presence();
                $actualPresence->setStatus($Null);
                $actualPresence->setUserTeacher($user);
                $actualPresence->setUserStudent($student);
                $actualPresence->setCourseSession($session);
                $actualPresence->setPeriod($period);
                $actualPresence->setDate($dateFormat);
                $this->em->persist($actualPresence);
                $this->em->flush();
                $Presences = $this->presenceRepo->OrderByStudent($session, $dateFormat, $period);
            }
        }

        $SameStatus = $this->createFormBuilder()

            ->add('singleStatus', 'entity', [
                'class' => 'FormaLibrePresenceBundle:Status',
                'property' => 'statusName',
                'empty_value' => ' Indiquer toute la classe comme:', ])
            ->add('valider', 'submit', [
                'label' => 'Comfirmer ?', ])
            ->getForm();

        $formCollection = new Releves();

        foreach ($Presences as $presence) {
            $formCollection->getReleves()->add($presence);
        }

        $presForm = $this->createForm(new CollReleveType(), $formCollection);

        if ($request->isMethod('POST')) {
            $presForm->handleRequest($request);

            foreach ($Presences as $presence) {
                $this->em->persist($presence);
            }

            $this->em->flush();

            return $this->redirect($this->generateUrl('formalibre_presence_releve',
                    ['period' => $period->getId(),
                          'date' => $date,
                          'session' => $session->getId(), ]));
        }

        return ['presForm' => $presForm->createView(),
                     'sameStatus' => $SameStatus->createView(),
                     'status' => $liststatus,
                     'user' => $user,
                     'presences' => $Presences,
                     'period' => $period,
                     'date' => $date,
                     'session' => $session,
                     'groups' => $Groups,
                     'users' => $Users,
                     'daypresences' => $dayPresences,
                     'canCheckPresences' => $canCkeckPresences, ];
    }

    /**
     * @EXT\Route(
     *     "/presence/archives",
     *     name="formalibre_presence_archives",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function ArchivesAction(User $user)
    {
        $canViewArchives = $this->presenceManager->checkRights($user, PresenceRights::READING_ARCHIVES);
        $canEditArchives = $this->presenceManager->checkRights($user, PresenceRights::EDIT_ARCHIVES);

        $SchoolYear = $this->schoolYearRepo->findOneBySchoolYearActual(true);
        if (!is_null($SchoolYear)) {
            $Presences = $this->presenceRepo->findBySchoolYear($SchoolYear);
            $SchoolYearSelection = $this->createFormBuilder()

                ->add('selection', 'entity', [
                    'class' => 'FormaLibrePresenceBundle:SchoolYear',
                    'property' => 'schoolYearName',
                    'empty_value' => ' Changer de période', ])
                ->add('valider', 'submit', [
                    'label' => 'Comfirmer ?', ])
                ->getForm();

            $Presences = $this->presenceRepo->findBySchoolYear($SchoolYear);

            $SchoolYearName = $SchoolYear->getSchoolYearName();

            $request = $this->getRequest();
            if ('POST' === $request->getMethod()) {
                $SchoolYearSelection->handleRequest($request);
                $name = $SchoolYearSelection->get('selection')->getData();
                $Presences = $this->presenceRepo->findBySchoolYear($name);
                $SchoolYearName = $name->getSchoolYearName();
            }
        } else {
            $Presences = $this->presenceRepo->findAll();
            $SchoolYearName = 'Aucune période existante/selectionnée';

            $SchoolYearSelection = $this->createFormBuilder()

                ->add('selection', 'entity', [
                    'class' => 'FormaLibrePresenceBundle:SchoolYear',
                    'property' => 'schoolYearName',
                    'empty_value' => ' Aucune période existante', ])
                ->add('valider', 'submit', [
                    'label' => 'Comfirmer', ])
                ->getForm();
        }

        return ['presences' => $Presences,
                        'canViewArchives' => $canViewArchives,
                        'canEditArchives' => $canEditArchives,
                        'schoolYear' => $SchoolYear,
                        'schoolYearName' => $SchoolYearName,
                        'schoolYearSelection' => $SchoolYearSelection->createView(), ];
    }

    /**
     * @EXT\Route(
     *     "/presence/presence_modif/id/{id}",
     *     name="formalibre_presence_modif",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceModifAction($id, $user)
    {
        $canEditArchives = $this->presenceManager->checkRights($user, PresenceRights::EDIT_ARCHIVES);

        $Presence = $this->presenceRepo->findOneById($id);
        $ModifPresenceForm = $this->createFormBuilder()
        ->add(
                 'Status',
                 'entity',
                 [
                     'multiple' => false,
                     'expanded' => false,
                     'label' => 'Status:',
                     'class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'data_class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'empty_value' => 'Nouveau status',
                     'property' => 'statusName',
                 ])
        ->add('Comment', 'textarea')
        ->add('Save', 'submit')
        ->getForm();

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $ModifPresenceForm->handleRequest($request);

            $NewStatus = $ModifPresenceForm->get('Status')->getData();
            $NewComment = $ModifPresenceForm->get('Comment')->getData();

            if (empty($NewStatus)) {
                $Presence->setComment($NewComment);
                $this->em->flush();

                return new JsonResponse('success', 200);
            } else {
                $Presence->setStatus($NewStatus);
                $Presence->setComment($NewComment);
                $this->em->flush();

                return new JsonResponse('success', 200);
            }
        }

        return ['ModifPresenceForm' => $ModifPresenceForm->createView(),
                    'presence' => $Presence,
                    'canEditArchives' => $canEditArchives, ];
    }

    /**
     * @EXT\Route(
     *     "/presence/presence_supp/id/{id}",
     *     name="formalibre_presence_supp",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceSuppAction($id)
    {
        $Presence = $this->presenceRepo->findOneById($id);

        return ['presence' => $Presence];
    }

    /**
     * @EXT\Route(
     *     "/presence/presence_supp_validate/id/{id}",
     *     name="formalibre_presence_supp_validate",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function PresenceSuppValidateAction($id)
    {
        $Presence = $this->presenceRepo->findOneById($id);

        $this->em->remove($Presence);
        $this->em->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/presence/listingstatus",
     *     name="formalibre_presence_listingstatus",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function ListingStatusAction()
    {
        $liststatus = $this->statuRepo->findAll();
        $datas = [];
        foreach ($liststatus as $status) {
            $datas[$status->getId()] = [];
            $datas[$status->getId()]['color'] = $status->getStatusColor();
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/presence/listingstatusbydefaultnoadmin",
     *     name="formalibre_presence_listingstatusbydefaultnoadmin",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function ListingStatusByDefaultnoAdminAction()
    {
        $liststatus = $this->statuRepo->findByStatusByDefault(0);
        $datas = [];
        foreach ($liststatus as $status) {
            $datas[$status->getId()] = [];
            $datas[$status->getId()] = $status->getId();
        }

        return new JsonResponse($datas, 200);
    }
}
