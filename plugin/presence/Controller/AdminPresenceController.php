<?php

namespace FormaLibre\PresenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use FormaLibre\PresenceBundle\Entity\PresenceRights;
use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Entity\SchoolYear;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\PresenceBundle\Manager\PresenceManager;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_presence_admin_tool')")
 */
class AdminPresenceController extends Controller
{
    private $om;
    private $em;
    private $presenceRepo;
    private $periodRepo;
    private $groupRepo;
    private $userRepo;
    private $schoolYearRepo;
    private $router;
    private $config;
    private $presenceManager;

    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "router"             = @DI\Inject("router"),
     *      "config"             = @DI\Inject("claroline.config.platform_config_handler"),
     *      "presenceManager"    = @DI\Inject("formalibre.manager.presence_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        EntityManager $em,
        RouterInterface $router,
        PlatformConfigurationHandler $config,
        PresenceManager $presenceManager
      ) {
        $this->router = $router;
        $this->om = $om;
        $this->em = $em;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->periodRepo = $om->getRepository('FormaLibrePresenceBundle:Period');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->statuRepo = $om->getRepository('FormaLibrePresenceBundle:Status');
        $this->schoolYearRepo = $om->getRepository('FormaLibrePresenceBundle:SchoolYear');
        $this->presenceRepo = $om->getRepository('FormaLibrePresenceBundle:Presence');
        $this->config = $config;
        $this->presenceManager = $presenceManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/presence/tool/index",
     *     name="formalibre_presence_admin_tool_index",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminToolIndexAction()
    {
        $rightsValue = array();

        $rightsForArray = $this->presenceManager->getAllPresenceRights();

        foreach ($rightsForArray as $oneRightForArray) {
            $mask = $oneRightForArray->getMask();
            $oneValue = array();
            $oneValue['right'] = $oneRightForArray;
            $oneValue[PresenceRights::PERSONAL_ARCHIVES] = (PresenceRights::PERSONAL_ARCHIVES & $mask) === PresenceRights::PERSONAL_ARCHIVES;
            $oneValue[PresenceRights::CHECK_PRESENCES] = (PresenceRights::CHECK_PRESENCES & $mask) === PresenceRights::CHECK_PRESENCES;
            $oneValue[PresenceRights::READING_ARCHIVES] = (PresenceRights::READING_ARCHIVES & $mask) === PresenceRights::READING_ARCHIVES;
            $oneValue[PresenceRights::EDIT_ARCHIVES] = (PresenceRights::EDIT_ARCHIVES & $mask) === PresenceRights::EDIT_ARCHIVES;
            $rightsValue[] = $oneValue;
        }

        $rightNameId = array();
        $rightNameId[] = PresenceRights::PERSONAL_ARCHIVES;
        $rightNameId[] = PresenceRights::CHECK_PRESENCES;
        $rightNameId[] = PresenceRights::READING_ARCHIVES;
        $rightNameId[] = PresenceRights::EDIT_ARCHIVES;

        $rightName = array();
        $rightName[PresenceRights::PERSONAL_ARCHIVES] = 'Voir ses archives';
        $rightName[PresenceRights::CHECK_PRESENCES] = 'Relever les présences';
        $rightName[PresenceRights::READING_ARCHIVES] = 'Consulter les archives';
        $rightName[PresenceRights::EDIT_ARCHIVES] = 'Editer les archives';

        $listStatus = $this->statuRepo->findAll();
        $NewStatusForm = $this->createFormBuilder()

            ->add('name', 'text')
            ->add('color', 'text')
            ->add('principalStatus', 'checkbox', array(
                  'required' => false, )
            )
            ->add('valider', 'submit', array(
                'label' => 'Ajouter', ))

            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() === 'POST') {
            $NewStatusForm->handleRequest($request);
            $name = $NewStatusForm->get('name')->getData();
            $color = $NewStatusForm->get('color')->getData();
            $principal = $NewStatusForm->get('principalStatus')->getData();

            $actualStatus = new Status();
            $actualStatus->setStatusName($name);
            $actualStatus->setStatusColor($color);
            $actualStatus->setStatusByDefault($principal);
            $this->em->persist($actualStatus);
            $this->em->flush();

            return $this->redirect($this->generateUrl('formalibre_presence_admin_tool_index'));
        }

        $ActualSchoolYear = $this->schoolYearRepo->findOneBySchoolYearActual(1);
        $AllSchoolYear = $this->schoolYearRepo->findAll();

        $NewSchoolYearForm = $this->createFormBuilder()

            ->add('name', 'text')
            ->add('beginDate', 'text')
            ->add('endDate', 'text')
            ->add('beginHour', 'text')
            ->add('endHour', 'text')
            ->add('actual', 'checkbox', array(
                  'required' => false, )
            )
            ->add('valider2', 'submit', array(
                'label' => 'Ajouter', ))

            ->getForm();

        return array('rightsForArray' => $rightsForArray,
                     'rightsValue' => $rightsValue,
                     'rightNameId' => $rightNameId,
                     'rightName' => $rightName,
                     'NewStatusForm' => $NewStatusForm->createView(),
                     'NewSchoolYearForm' => $NewSchoolYearForm->createView(),
                     'listStatus' => $listStatus,
                     'allSchoolYear' => $AllSchoolYear,
                     'actualSchoolYear' => $ActualSchoolYear, );
    }

    /**
     * @EXT\Route(
     *     "/admin/presence/addSchoolYear/index",
     *     name="formalibre_presence_admin_add_school_year",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminAddSchoolYearAction()
    {
        $NewSchoolYearForm = $this->createFormBuilder()

            ->add('name', 'text')
            ->add('beginDate', 'text')
            ->add('endDate', 'text')
            ->add('beginHour', 'text')
            ->add('endHour', 'text')
            ->add('actual', 'checkbox', array(
                  'required' => false, )
            )
            ->add('valider2', 'submit', array(
                'label' => 'Ajouter', ))

            ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() === 'POST') {
            $NewSchoolYearForm->handleRequest($request);

            $name = $NewSchoolYearForm->get('name')->getData();
            $beginDate = $NewSchoolYearForm->get('beginDate')->getData();
            $endDate = $NewSchoolYearForm->get('endDate')->getData();
            $beginHour = $NewSchoolYearForm->get('beginHour')->getData();
            $endHour = $NewSchoolYearForm->get('endHour')->getData();
            $actual = $NewSchoolYearForm->get('actual')->getData();

            $beginDateFormat = \DateTime::createFromFormat('d-m-Y', $beginDate);
            $endDateFormat = \DateTime::createFromFormat('d-m-Y', $endDate);
            $beginHourFormat = \DateTime::createFromFormat('H:i', $beginHour);
            $endHourFormat = \DateTime::createFromFormat('H:i', $endHour);

            if ($actual) {
                $AllSchoolYear = $this->schoolYearRepo->findAll();
                foreach ($AllSchoolYear as $oneSchoolYear) {
                    $oneSchoolYear->setSchoolYearActual(false);
                }
            }
            $actualSchoolYear = new SchoolYear();
            $actualSchoolYear->setSchoolYearName($name);
            $actualSchoolYear->setSchoolYearBegin($beginDateFormat);
            $actualSchoolYear->setSchoolYearEnd($endDateFormat);
            $actualSchoolYear->setSchoolDayBeginHour($beginHourFormat);
            $actualSchoolYear->setSchoolDayEndHour($endHourFormat);
            $actualSchoolYear->setSchoolYearActual($actual);
            $this->em->persist($actualSchoolYear);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('formalibre_presence_admin_tool_index'));
    }

    /**
     * @EXT\Route(
     *     "/presence/schoolYear_modif/id/{theSchoolYear}",
     *     name="formalibre_school_year_modif",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function SchoolYearModifAction(SchoolYear $theSchoolYear)
    {
        $ModifSchoolYearForm = $this->createFormBuilder()

            ->add('nameModifSchoolYear', 'text')
            ->add('beginDateModifSchoolYear', 'text')
            ->add('endDateModifSchoolYear', 'text')
            ->add('beginHourModifSchoolYear', 'text')
            ->add('endHourModifSchoolYear', 'text')
            ->add('actualModifSchoolYear', 'checkbox', array(
                  'required' => false, )
            )
            ->add('validerModifSchoolYear', 'submit', array(
                'label' => 'Ajouter', ))

            ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() === 'POST') {
            $ModifSchoolYearForm->handleRequest($request);

            $modifName = $ModifSchoolYearForm->get('nameModifSchoolYear')->getData();
            $modifBeginDate = $ModifSchoolYearForm->get('beginDateModifSchoolYear')->getData();
            $modifEndDate = $ModifSchoolYearForm->get('endDateModifSchoolYear')->getData();
            $modifBeginHour = $ModifSchoolYearForm->get('beginHourModifSchoolYear')->getData();
            $modifEndHour = $ModifSchoolYearForm->get('endHourModifSchoolYear')->getData();
            $modifActual = $ModifSchoolYearForm->get('actualModifSchoolYear')->getData();

            if ($modifActual) {
                $AllSchoolYear = $this->schoolYearRepo->findAll();
                foreach ($AllSchoolYear as $oneSchoolYear) {
                    $oneSchoolYear->setSchoolYearActual(false);
                }
            }

            $beginDateFormat = \DateTime::createFromFormat('d-m-Y', $modifBeginDate);
            $endDateFormat = \DateTime::createFromFormat('d-m-Y', $modifEndDate);
            $beginHourFormat = \DateTime::createFromFormat('H:i', $modifBeginHour);
            $endHourFormat = \DateTime::createFromFormat('H:i', $modifEndHour);

            $theSchoolYear->setSchoolYearName($modifName);
            $theSchoolYear->setSchoolYearBegin($beginDateFormat);
            $theSchoolYear->setSchoolYearEnd($endDateFormat);
            $theSchoolYear->setSchoolDayBeginHour($beginHourFormat);
            $theSchoolYear->setSchoolDayEndHour($endHourFormat);
            $theSchoolYear->setSchoolYearActual($modifActual);
            $this->em->persist($theSchoolYear);
            $this->em->flush();

            return new JsonResponse('success', 200);
        }

        return array('ModifSchoolYearForm' => $ModifSchoolYearForm->createView(),
                    'theSchoolYear' => $theSchoolYear, );
    }
    /**
     * @EXT\Route(
     *     "/presence/schoolYear_supprimer/theSchoolYear/{theSchoolYear}",
     *     name="formalibre_school_year_supprimer",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function SchoolYearSupprimerAction(SchoolYear $theSchoolYear)
    {
        $this->em->remove($theSchoolYear);
        $this->em->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/presence/right/right/{right}/rightValue/{rightValue}",
     *     name="formalibre_presence_admin_right",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminRightAction(PresenceRights $right, $rightValue)
    {
        $mask = $right->getMask();
        $newmask = $mask ^ $rightValue;

        $right->setMask($newmask);
        $this->om->persist($right);
        $this->om->flush();

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/presence/horaire",
     *     name="formalibre_presence_horaire",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function adminHoraireAction()
    {
        $Periods = $this->periodRepo->findAll();
        $SchoolYear = $this->schoolYearRepo->findOneBySchoolYearActual(true);

        if (!is_null($SchoolYear)) {
            $SchoolYearBeginHour = $SchoolYear->getSchoolDayBeginHour();
            $SchoolYearEndHour = $SchoolYear->getSchoolDayEndHour();
        } else {
            $SchoolYearBeginHour = '08:00:00';
            $SchoolYearEndHour = '18:00:00';
        }

        $NewPeriodForm = $this->createFormBuilder()

            ->add('day', 'choice', array(
                    'choices' => array(
                    'monday' => 'lundi',
                    'tuesday' => 'mardi',
                    'wednesday' => 'mercredi',
                    'thursday' => 'jeudi',
                    'friday' => 'vendredi',
                    'saturday' => 'samedi',
                    ),
                'multiple' => true,
                'expanded' => true,
                ))
            ->add('number', 'text')
            ->add('name', 'text')
            ->add('start', 'text')
            ->add('end', 'text')
            ->add('valider', 'submit', array(
                'label' => 'Ajouter', ))

            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() === 'POST') {
            $NewPeriodForm->handleRequest($request);
            $startHour = $NewPeriodForm->get('start')->getData();
            $endHour = $NewPeriodForm->get('end')->getData();
            $name = $NewPeriodForm->get('name')->getData();
            $number = $NewPeriodForm->get('number')->getData();
            $wichDay = $NewPeriodForm->get('day')->getData();

            $startHourFormat = \DateTime::createFromFormat('H:i', $startHour);
            $endHourFormat = \DateTime::createFromFormat('H:i', $endHour);

            if (!is_null($SchoolYear)) {
                $BeginSchoolYearDate = $SchoolYear->getSchoolYearBegin();
                $EndSchoolYearDate = $SchoolYear->getSchoolYearEnd();

                foreach ($wichDay as $oneDay) {
                    $begin = $BeginSchoolYearDate;
                    $begin->modify('last '.$oneDay);
                    $interval = new \DateInterval('P1W'); //interval d'une semaine
                        $end = $EndSchoolYearDate;
                    $end->modify('next '.$oneDay); //dernier jour du mois
                        $period = new \DatePeriod($begin, $interval, $end);
                    foreach ($period as $date) {
                        $dateFormat = $date->format('Y-m-d');
                        $dayNameFormat = $date->format('l');

                        $actualPeriod = new Period();
                        $actualPeriod->setBeginHour($startHourFormat);
                        $actualPeriod->setEndHour($endHourFormat);
                        $actualPeriod->setDay($date);
                        $actualPeriod->setDayName($dayNameFormat);
                        $actualPeriod->setName($name);
                        $actualPeriod->setNumPeriod($number);
                        $actualPeriod->setSchoolYearId($SchoolYear);

                        $this->em->persist($actualPeriod);
                        $this->em->flush();
                    }
                }
            } else {
                $session = $request->getSession();
                $session->getFlashBag()->add('error', "Vous n'avez pas selectionner l'année courante dans le menu de configuration");
            }

            return $this->redirect($this->generateUrl('formalibre_presence_horaire'));
        }

        return array('NewPeriodForm' => $NewPeriodForm->createView(),
                    'periods' => $Periods,
                    'schoolYear' => $SchoolYear,
                    'schoolYearBeginHour' => $SchoolYearBeginHour,
                    'schoolYearEndHour' => $SchoolYearEndHour, );
    }

    /**
     * @EXT\Route(
     *     "/admin/presence/modifier_horaire/period/{period}",
     *     name="formalibre_presence_modifier_horaire",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function adminModifierHoraireAction(Period $period)
    {
        $ModifPeriodForm = $this->createFormBuilder()

            ->add('numberMod', 'text')
            ->add('nameMod', 'text')
            ->add('startMod', 'text')
            ->add('endMod', 'text')
            ->add('dayName', 'hidden')
            ->add('modifier', 'submit')
            ->getForm();

        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $ModifPeriodForm->handleRequest($request);

            $startHour = $ModifPeriodForm->get('startMod')->getData();
            $endHour = $ModifPeriodForm->get('endMod')->getData();
            $name = $ModifPeriodForm->get('nameMod')->getData();
            $number = $ModifPeriodForm->get('numberMod')->getData();
            $dayName = $ModifPeriodForm->get('dayName')->getData();

            $startHourFormat = \DateTime::createFromFormat('H:i', $startHour);
            $endHourFormat = \DateTime::createFromFormat('H:i', $endHour);

            $PeriodToModif = $this->periodRepo->findBy(array('beginHour' => $startHourFormat,
                                                                 'endHour' => $endHourFormat,
                                                                 'dayName' => $dayName, ));

            foreach ($PeriodToModif as $OnePeriodToModif) {
                $OnePeriodToModif->setBeginHour($startHourFormat);
                $OnePeriodToModif->setEndHour($endHourFormat);
                $OnePeriodToModif->setName($name);
                $OnePeriodToModif->setNumPeriod($number);
            }
            $this->em->flush();

            return new JsonResponse('success', 200);
        }

        return array('ModifPeriodForm' => $ModifPeriodForm->createView(), 'period' => $period);
    }

    /**
     * @EXT\Route(
     *     "/admin/period_supprimer/period/{period}",
     *     name="formalibre_period_supprimer",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function adminPeriodSupprimerAction(Period $period)
    {
        $startHour = $period->getBeginHour();
        $endHour = $period->getEndHour();
        $dayName = $period->getDayName();

        $PeriodToModif = $this->periodRepo->findBy(array('beginHour' => $startHour,
                                                         'endHour' => $endHour,
                                                         'dayName' => $dayName, ));

        foreach ($PeriodToModif as $OnePeriodToModif) {
            $this->em->remove($OnePeriodToModif);
        }

        $this->em->flush();

        return new RedirectResponse($this->router->generate('formalibre_presence_horaire'));
    }

    /**
     * @EXT\Route(
     *     "/admin/listing/roles",
     *     name="formalibre_admin_listing_roles",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminListingRolesAction()
    {
        return array();
    }
    /**
     * @EXT\Route(
     *     "/presence/status_modif/id/{theStatus}",
     *     name="formalibre_status_modif",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     * @EXT\Template()
     */
    public function StatusModifAction(Status $theStatus)
    {
        $ModifStatusForm = $this->createFormBuilder()

            ->add('name2', 'text')
            ->add('color2', 'text')
            ->add('principalStatus2', 'checkbox', array(
                  'required' => false, )
            )
            ->add('valider2', 'submit', array(
                'label' => 'Modifier', ))

            ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $ModifStatusForm->handleRequest($request);

            $NewName = $ModifStatusForm->get('name2')->getData();
            $NewColor = $ModifStatusForm->get('color2')->getData();
            $NewByDefault = $ModifStatusForm->get('principalStatus2')->getData();

            $theStatus->setStatusName($NewName);
            $theStatus->setStatusColor($NewColor);
            $theStatus->setStatusByDefault($NewByDefault);
            $this->em->persist($theStatus);
            $this->em->flush();

            return new JsonResponse('success', 200);
        }

        return array('ModifStatusForm' => $ModifStatusForm->createView(),
                    'theStatus' => $theStatus, );
    }
    /**
     * @EXT\Route(
     *     "/presence/status_supprimerf/id/{theStatus}",
     *     name="formalibre_status_supprimer",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function StatussupprimerAction(Status $theStatus)
    {
        $this->em->remove($theStatus);
        $this->em->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/presence/listingstatusbydefault",
     *     name="formalibre_presence_listingstatusbydefault",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function ListingStatusByDefaultAction()
    {
        $liststatus = $this->statuRepo->findByStatusByDefault(0);
        $datas = array();
        foreach ($liststatus as $status) {
            $datas[$status->getId()] = array();
            $datas[$status->getId()] = $status->getId();
        }

        return new JsonResponse($datas, 200);
    }
}
