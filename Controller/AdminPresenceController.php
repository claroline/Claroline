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
use Claroline\CoreBundle\Manager\RoleManager;

use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Entity\Releves;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\PresenceBundle\Manager\PresenceManager;

use FormaLibre\PresenceBundle\Form\Type\ReleveType;
use FormaLibre\PresenceBundle\Form\Type\CollReleveType;
use FormaLibre\PresenceBundle\Entity\PresenceRights;



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
    private $router;
    private $config;
    private $roleManager;
    private $presenceManager;
    
    
    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "router"             = @DI\Inject("router"),
     *      "config"             = @DI\Inject("claroline.config.platform_config_handler"),
     *      "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *      "presenceManager"    = @DI\Inject("formalibre.manager.presence_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        EntityManager $em,
        RouterInterface $router,
        PlatformConfigurationHandler $config,
        RoleManager $roleManager,
        PresenceManager $presenceManager
      )
    {   $this->router             =$router;          
        $this->om                 = $om;
        $this->em                 = $em;  
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User');  
        $this->periodRepo         = $om->getRepository('FormaLibrePresenceBundle:Period');
        $this->groupRepo          = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User'); 
        $this->statuRepo          = $om->getRepository('FormaLibrePresenceBundle:Status');  
        $this->presenceRepo       = $om->getRepository('FormaLibrePresenceBundle:Presence');  
        $this->config             = $config;
        $this->roleManager        = $roleManager;
        $this->presenceManager    = $presenceManager;
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
        $rightsValue=array();
        
        $rightsForArray=$this->presenceManager->getAllPresenceRights();
        
        foreach ($rightsForArray as $oneRightForArray){
            
            $mask=$oneRightForArray->getMask();
            $oneValue=array();
            $oneValue["right"]=$oneRightForArray;
            $oneValue[PresenceRights::PERSONAL_ARCHIVES]= (PresenceRights::PERSONAL_ARCHIVES & $mask)===PresenceRights::PERSONAL_ARCHIVES;
            $oneValue[PresenceRights::CHECK_PRESENCES]=(PresenceRights::CHECK_PRESENCES & $mask)===PresenceRights::CHECK_PRESENCES;
            $oneValue[PresenceRights::READING_ARCHIVES]=(PresenceRights::READING_ARCHIVES & $mask)===PresenceRights::READING_ARCHIVES;
            $oneValue[PresenceRights::EDIT_ARCHIVES]=(PresenceRights::EDIT_ARCHIVES & $mask)===PresenceRights::EDIT_ARCHIVES;
            $rightsValue[]=$oneValue;
          
            
        }
        
        $rightNameId=array();
        $rightNameId[]=  PresenceRights::PERSONAL_ARCHIVES;
        $rightNameId[]=  PresenceRights::CHECK_PRESENCES;
        $rightNameId[]=  PresenceRights::READING_ARCHIVES;
        $rightNameId[]=  PresenceRights::EDIT_ARCHIVES;
        
        $rightName=array();
        $rightName[PresenceRights::PERSONAL_ARCHIVES]="Voir ses archives";
        $rightName[PresenceRights::CHECK_PRESENCES]="Relever les présences";
        $rightName[PresenceRights::READING_ARCHIVES]="Consulter les archives";
        $rightName[PresenceRights::EDIT_ARCHIVES]="Editer les archives";
        
        return array('rightsForArray'=>$rightsForArray, 'rightsValue'=>$rightsValue, 'rightNameId'=>$rightNameId, 'rightName'=>$rightName);
         
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
    public function adminRightAction(PresenceRights $right, $rightValue){
        
        $mask=$right->getMask();
        $newmask=$mask ^ $rightValue;
           
        $right->setMask($newmask);
        $this->om->persist($right);
        $this->om->flush();
        
        return new Response('success',200);
                
        
        
        
    }
    
    
             /**
     * @EXT\Route(
     *     "/admin/presence/horaire",
     *     name="formalibre_presence_horaire",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminHoraireAction()
            
    {
       $Periods = $this->periodRepo->findByVisibility(1) ; 
        
       $NewPeriodForm = $this ->createFormBuilder()
        
            ->add('day', 'choice', array(
                    'choices'   => array(
                    'monday'   => 'lundi',
                    'tuesday' => 'mardi',
                    'wednesday'   => 'mercredi',
                    'thursday'   => 'jeudi',
                    'friday'   => 'vendredi',
                    'saturday'   => 'samedi',
                    ),
                'multiple'  => true,
                'expanded'  => true,
                ))
            ->add('number','text')  
            ->add('name','text')
            ->add('start','text')
            ->add('end','text')
            ->add ('valider','submit',array (
                'label'=>'Ajouter'))
               
            ->getForm();

            $request = $this->getRequest();
            if ($request->getMethod() === 'POST') {
                
                $NewPeriodForm->handleRequest($request);
                $startHour = $NewPeriodForm->get("start")->getData();
                $endHour = $NewPeriodForm->get("end")->getData();
                $name = $NewPeriodForm->get("name")->getData();
                $number = $NewPeriodForm->get("number")->getData();
                $wichDay =$NewPeriodForm->get("day")->getData();
                
                $startHourFormat = \DateTime::createFromFormat('H:i', $startHour);
                $endHourFormat = \DateTime::createFromFormat('H:i', $endHour);
                
                foreach ($wichDay as $oneDay) {
                    $begin = new \DateTime('2015-09-01 09:00:00', new \DateTimeZone('Europe/Paris')); //j'initialise ainsi car je ne suis pas le 1/09
                    $begin->modify('last '.$oneDay);  
                    $interval = new \DateInterval('P1W'); //interval d'une semaine
                    $end = new \DateTime('2016-06-30 09:00:00', new \DateTimeZone('Europe/Paris'));
                    $end->modify('next '.$oneDay); //dernier jour du mois
                    $period = new \DatePeriod($begin, $interval, $end);
                    foreach ($period as $date) {
                         
                        $dateFormat = $date->format("Y-m-d");
                        $dayNameFormat=$date->format("l");

                        $actualPeriod = new Period();
                        $actualPeriod->setBeginHour($startHourFormat);
                        $actualPeriod->setEndHour($endHourFormat);
                        $actualPeriod->setDay($date);
                        $actualPeriod->setDayName($dayNameFormat);
                        $actualPeriod->setName($name);
                        $actualPeriod->setNumPeriod($number);

                        $this->em->persist($actualPeriod);
                        $this->em->flush();  
                    }
                }
   
            return $this->redirect($this->generateUrl('formalibre_presence_horaire'));    
        }  
       return array('NewPeriodForm' => $NewPeriodForm->createView(), 'periods' => $Periods);
    }
    
     
             /**
     * @EXT\Route(
     *     "/admin/presence/modifier_horaire/period/{period}",
     *     name="formalibre_presence_modifier_horaire",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminModifierHoraireAction(Period $period)
            
    { 
       $ModifPeriodForm = $this ->createFormBuilder()
        
            ->add('numberMod','text')  
            ->add('nameMod','text')
            ->add('startMod','text')
            ->add('endMod','text')
            ->add('dayName','hidden')
            ->add ('modifier','submit')
            ->getForm();

                $request = $this->get('request');
                
                if($request->getMethod()=='POST'){
                $ModifPeriodForm->handleRequest($request);
               
                $startHour = $ModifPeriodForm->get("startMod")->getData();
                $endHour = $ModifPeriodForm->get("endMod")->getData();
                $name = $ModifPeriodForm->get("nameMod")->getData();
                $number = $ModifPeriodForm->get("numberMod")->getData();
                $dayName =$ModifPeriodForm->get("dayName")->getData();
                
                $startHourFormat = \DateTime::createFromFormat('H:i', $startHour);
                $endHourFormat = \DateTime::createFromFormat('H:i', $endHour);
                                
                $PeriodToModif = $this->periodRepo->findBy(array('beginHour'=>$startHourFormat,
                                                                 'endHour'=>$endHourFormat, 
                                                                 'dayName'=>$dayName));

                    foreach ($PeriodToModif as $OnePeriodToModif) {

                        $OnePeriodToModif->setBeginHour($startHourFormat);
                        $OnePeriodToModif->setEndHour($endHourFormat);
                        $OnePeriodToModif->setName($name);
                        $OnePeriodToModif->setNumPeriod($number); 
                    }    
                    $this->em->flush();

             return new JsonResponse('success',200);
                    
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
     * @param User $user
     */
    public function adminPeriodSupprimerAction(Period $period){
        
        
        
        $startHour = $period->getBeginHour();
        $endHour = $period->getEndHour(); 
        $dayName = $period->getDayName();
        
        $PeriodToModif = $this->periodRepo->findBy(array('beginHour'=>$startHour,
                                                         'endHour'=>$endHour, 
                                                         'dayName'=>$dayName));

        foreach ($PeriodToModif as $OnePeriodToModif) {
            
            $OnePeriodToModif->setVisibility(0);
           
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
    public function adminListingRolesAction(){
        
        return array();
 
    }    
    
    
}


    


