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

use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Entity\Releves;
use Claroline\CoreBundle\Entity\User;

use FormaLibre\PresenceBundle\Form\Type\ReleveType;
use FormaLibre\PresenceBundle\Form\Type\CollReleveType;



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
    
    
    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     */
    public function __construct(
        ObjectManager $om,
        EntityManager $em
      )
    {
        $this->om                 = $om;
        $this->em                 = $em;  
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User');  
        $this->periodRepo         = $om->getRepository('FormaLibrePresenceBundle:Period');
        $this->groupRepo          = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User'); 
        $this->statuRepo          = $om->getRepository('FormaLibrePresenceBundle:Status');  
        $this->presenceRepo       = $om->getRepository('FormaLibrePresenceBundle:Presence');  
    }
    
       /**
     * @EXT\Route(
     *     "/admin/presence/tool/index",
     *     name="formalibre_presence_admin_tool_index",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminToolIndexAction(User $user)
    {
 
        $Presences = $this->presenceRepo->findAll() ;
        $Periods = $this->periodRepo->findByVisibility(true) ;
        
        
        
        return array('user'=>$user, 'presences'=>$Presences, 'periods'=>$Periods );
         
    }
          /**
     * @EXT\Route(
     *     "/admin/presence/choix_classe/period/{period}/date/{date}",
     *     name="formalibre_choix_classe",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminChoixClasseAction(User $user, Period $period, Request $request, $date)

    {
        $form = $this ->createFormBuilder()
        
            ->add ('selection','entity',array (
                'label'=>'Classe:',
                'class' => 'ClarolineCoreBundle:Group',
                'property' => 'name',
                'empty_value' =>'Choisissez une classe',))
            ->add ('valider','submit',array (
                'label'=>'Relever les présences'))
            ->getForm();

            $request = $this->getRequest();
            if ($request->getMethod() == 'POST')
            {
                $form->handleRequest($request);
                $classe = $form->get("selection")->getData();
                
                return $this->redirect($this->generateUrl('formalibre_presence_releve', array("period" => $period->getId(), "date" => $date, "classe" => $classe->getId())));
        }
            
            return array('form'=>$form->createView(),'user'=>$user,'period'=>$period, 'date'=>$date);
  
    }
    
      /**
     * @EXT\Route(
     *     "/admin/presence/releve/period/{period}/date/{date}/classe/{classe}",
     *     name="formalibre_presence_releve",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminPresenceReleveAction(Request $request, User $user, Period $period, $date, Group $classe)
    {
        $dateFormat=new \DateTime($date);
 
        $Presences = $this->presenceRepo->OrderByStudent($classe,$date,$period);
        $dayPresences = $this->presenceRepo->OrderByNumPeriod($classe,$date);
        
        $Groups = $this->groupRepo->findAll() ;
        $Users = $this->userRepo->findByGroup($classe) ;
       
        $Null = $this->statuRepo->findOneByStatusName('');
        $liststatus= $this->statuRepo->findByStatusByDefault(false);
       
            if (!$Presences)
            {
                $Presences=array();
                foreach ($Users as $student)

                    {
                    $actualPresence =new Presence();
                    $actualPresence->setStatus($Null);
                    $actualPresence->setUserTeacher($user);
                    $actualPresence->setUserStudent($student);
                    $actualPresence->setGroup($classe);
                    $actualPresence->setPeriod($period);
                    $actualPresence->setDate($dateFormat);
                    $this->em->persist($actualPresence);
                    $this->em->flush();
                    $Presences[]=$actualPresence;
                    }
            }
            
        $SameStatus = $this ->createFormBuilder()
        
            ->add ('singleStatus','entity',array (
                'class' => 'FormaLibrePresenceBundle:Status',
                'property' => 'statusName',
                'empty_value' =>' Indiquer toute la classe comme:',))
            ->add ('valider','submit',array (
                'label'=>'Comfirmer ?'))
            ->getForm();
        
        $formCollection = new Releves;
        
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
                    array("period" => $period->getId(), 
                          "date" => $date, 
                          "classe" => $classe->getId())));
        }
        
        return array('presForm'=>$presForm->createView(),
                     'sameStatus'=>$SameStatus->createView(),
                     'status'=>$liststatus, 
                     'user'=>$user, 
                     'presences'=>$Presences, 
                     'period'=>$period, 
                     'date'=>$date, 
                     'classe'=>$classe, 
                     'groups'=>$Groups, 
                     'users'=>$Users,
                     'daypresences'=>$dayPresences);   
    }

    
           /**
     * @EXT\Route(
     *     "/admin/presence/archives",
     *     name="formalibre_presence_archives",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminArchivesAction()
            
    {
        $Presences = $this->presenceRepo->findAll();
       
       return array('presences'=> $Presences);
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
       $Periods = $this->periodRepo->findAll() ; 
        
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
            ->add ('supprimer','submit')   
            ->getForm();

                $request = $this->getRequest();

                $ModifPeriodForm->handleRequest($request);
                if($ModifPeriodForm->isSubmitted()){
                                    
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
               
                if ($ModifPeriodForm->get("modifier")->isClicked()) {
                    throw new \Exception("coucou");
//                    foreach ($PeriodToModif as $OnePeriodToModif) {
//
//                        $OnePeriodToModif->setBeginHour($startHourFormat);
//                        $OnePeriodToModif->setEndHour($endHourFormat);
//                        $OnePeriodToModif->setName($name);
//                        $OnePeriodToModif->setNumPeriod($number); 
//                    }    
//                    $this->em->flush();
                
                    
                }
             return new JsonResponse('success',200);
                    
            }    
          
       return array('ModifPeriodForm' => $ModifPeriodForm->createView(), 'period' => $period);
    }
    
    
    
                /**
     * @EXT\Route(
     *     "/admin/presence/presence_modif/id/{id}",
     *     name="formalibre_presence_modif",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminPresenceModifAction($id)
            
    {   $Presence= $this->presenceRepo->findOneById($id) ;
        $ModifPresenceForm = $this ->createFormBuilder()
        ->add (
                 'Status',
                 'entity',
                 array (
                     'multiple'  => false,
                     'expanded'  => false, 
                     'label'=>'Status:',
                     'class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'data_class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'empty_value'=> 'Nouveau status',
                     'property' => 'statusName'

                 ))
        ->add ('Comment','textarea')
        ->add ('Save','submit')
        ->getForm();
        
        
        $request = $this->getRequest();
         
        if ($request->getMethod() == 'POST')
            {
                $ModifPresenceForm->handleRequest($request);
                                                            
                $NewStatus = $ModifPresenceForm->get("Status")->getData();
                $NewComment = $ModifPresenceForm->get("Comment")->getData();
                
                if(empty($NewStatus)){
                    $Presence->setComment($NewComment);
                    $this->em->flush();
                    
                    return new JsonResponse('success',200);
                }
                else{
                    $Presence->setStatus($NewStatus);
                    $Presence->setComment($NewComment);
                    $this->em->flush();
                    
                    return new JsonResponse('success',200);
                }

                }
        
       return array('ModifPresenceForm' => $ModifPresenceForm->createView(),'presence' => $Presence);
    }
    
            /**
     * @EXT\Route(
     *     "/admin/presence/configurations",
     *     name="formalibre_presence_configurations",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function adminConfigurationsAction()
            
    {
       return array();
    }
    
    
            /**
     * @EXT\Route(
     *     "/admin/presence/listingstatus",
     *     name="formalibre_presence_listingstatus",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     */
    public function adminListingStatusAction()
            
    {
        
        $liststatus= $this->statuRepo->findAll();
        $datas=array();
        foreach($liststatus as $status)
        {
            $datas[$status->getId()]=array();
            $datas[$status->getId()]['color']=$status->getStatusColor();
        }
        return new JsonResponse($datas,200);
    }
    
    
    
}

