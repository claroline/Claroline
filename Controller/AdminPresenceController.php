<?php

namespace FormaLibre\PresenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;

use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use FormaLibre\PresenceBundle\Entity\Status;
use FormaLibre\PresenceBundle\Entity\FormColl;
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
    private $presenceRepo;
    private $periodRepo;
    private $groupRepo;
    private $userRepo;
    
    
    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(
        ObjectManager $om
      )
    {
        $this->om                 = $om;
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
        $Periods = $this->periodRepo->findAll() ;
        
        
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
 
        $Presences = $this->presenceRepo->findBy(array('period' => $period, 'date' =>$dateFormat, 'group' =>$classe)) ;
        $Periods = $this->periodRepo->findAll() ;
        $Groups = $this->groupRepo->findAll() ;
        $Users = $this->userRepo->findByGroup($classe) ;
        $Pre = $this->statuRepo->findOneByStatusName('Présent');
        $Ret = $this->statuRepo->findOneByStatusName('Retard');
        $Abs = $this->statuRepo->findOneByStatusName('Absent');
        $Null = $this->statuRepo->findOneByStatusName('NR');
        $liststatus= $this->statuRepo->findByStatusByDefault(false);
       
            if (!$Presences)
            {
                $Presences=array();
                foreach ($Users as $student)

                    {
                    $em = $this->getDoctrine()->getEntityManager();
                    $actualPresence =new Presence();
                    $actualPresence->setStatus($Null);
                    $actualPresence->setUserTeacher($user);
                    $actualPresence->setUserStudent($student);
                    $actualPresence->setGroup($classe);
                    $actualPresence->setPeriod($period);
                    $actualPresence->setDate($dateFormat);
                    $em->persist($actualPresence);
                    $em->flush();
                    $Presences[]=$actualPresence;
                    }
            } 

        $formCollection = new FormColl;
        
        foreach ($Presences as $presence) {
            $formCollection->getPresFormColl()->add($presence);
        }
        
        $presform = $this->createForm(new CollReleveType(), $formCollection);

//        $presForm = $this->get('form.factory')->create(new ReleveType());
        
        $presForm->handleRequest($request);
        
                if ($presForm->get('Pres')->isClicked())
                {  
                        $idPresence = $presForm->get("idPresence")->getData();
                        $ActualPresence = $this->presenceRepo->findOneById($idPresence);
                    
                        $em = $this->getDoctrine()->getEntityManager();
                        $ActualPresence->setStatus($Pre);
                        $em->flush();
                }

                else if ($presForm->get('Abs')->isClicked())
                 {  
                        $idPresence = $presForm->get("idPresence")->getData();
                        $ActualPresence = $this->presenceRepo->findOneById($idPresence);
                    
                        $em = $this->getDoctrine()->getEntityManager();
                        $ActualPresence->setStatus($Abs);
                        $em->flush();
                }
                
                else if ($presForm->get('Ret')->isClicked())
                 {  
                        $idPresence = $presForm->get("idPresence")->getData();
                        $ActualPresence = $this->presenceRepo->findOneById($idPresence);
                    
                        $em = $this->getDoctrine()->getEntityManager();
                        $ActualPresence->setStatus($Ret);
                        $em->flush();
                }
//                  else
//                  {
//                  foreach ($liststatus as $actualStatus) 
//                     {
//                        if ($presForm->get($actualStatus->getStatusName())->isClicked())
//                        {  
//                        $idPresence = $presForm->get("idPresence")->getData();
//                        $ActualPresence = $this->presenceRepo->findOneById($idPresence);
//                    
//                        $em = $this->getDoctrine()->getEntityManager();
//                        $ActualPresence->setStatus($actualStatus);
//                        $em->flush();
//                        break;
//                         }
//                     }
//                  }
  
        return array('presForm'=>$presForm->createView(),'status'=>$liststatus, 'user'=>$user, 'presences'=>$Presences, 'period'=>$period, 'date'=>$date, 'classe'=>$classe, 'groups'=>$Groups, 'users'=>$Users );   
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
       return array();
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
      $Periods = $this->periodRepo->findAll() ;
        
       return array('periods'=>$Periods);
    }
    
}

