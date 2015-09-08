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

use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Entity\Releves;
use Claroline\CoreBundle\Entity\User;

use FormaLibre\PresenceBundle\Form\Type\ReleveType;
use FormaLibre\PresenceBundle\Form\Type\CollReleveType;


class PresenceController extends Controller
{
    private $om;
    private $em;
    private $presenceRepo;
    private $periodRepo;
    private $groupRepo;
    private $userRepo;
    private $router;
    
    
    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "router"             = @DI\Inject("router"),
     * })
     */
    public function __construct(
        ObjectManager $om,
        EntityManager $em,
        RouterInterface $router
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
    }
    
       /**
     * @EXT\Route(
     *     "/presence/tool/index",
     *     name="formalibre_presence_tool_index",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function ToolIndexAction(User $user)
    {
 
        $Presences = $this->presenceRepo->findAll() ;
        $Periods = $this->periodRepo->findByVisibility(true) ;
        
        
        
        return array('user'=>$user, 'presences'=>$Presences, 'periods'=>$Periods );
         
    }
          /**
     * @EXT\Route(
     *     "/presence/choix_classe/period/{period}/date/{date}",
     *     name="formalibre_choix_classe",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function ChoixClasseAction(User $user, Period $period, Request $request, $date)

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
     *     "/presence/releve/period/{period}/date/{date}/classe/{classe}",
     *     name="formalibre_presence_releve",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceReleveAction(Request $request, User $user, Period $period, $date, Group $classe)
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
     *     "/presence/archives",
     *     name="formalibre_presence_archives",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function ArchivesAction()
            
    {
        $Presences = $this->presenceRepo->findAll();
       
       return array('presences'=> $Presences);
    }
  
         
                /**
     * @EXT\Route(
     *     "/presence/presence_modif/id/{id}",
     *     name="formalibre_presence_modif",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceModifAction($id)
            
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
     *     "/presence/presence_supp/id/{id}",
     *     name="formalibre_presence_supp",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     * @EXT\Template()
     */
    public function PresenceSuppAction($id){
        
        $Presence= $this->presenceRepo->findOneById($id) ;
     
        return array('presence' => $Presence);
        
    }
                    /**
     * @EXT\Route(
     *     "/presence/presence_supp_validate/id/{id}",
     *     name="formalibre_presence_supp_validate",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     */
    
    public function PresenceSuppValidateAction($id){
        
        $Presence= $this->presenceRepo->findOneById($id) ;
        
        $this->em->remove($Presence);
        $this->em->flush();
        
        return new JsonResponse("success",200);   
    }
   
    
    
            /**
     * @EXT\Route(
     *     "/presence/listingstatus",
     *     name="formalibre_presence_listingstatus",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User $user
     */
    public function ListingStatusAction()
            
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

