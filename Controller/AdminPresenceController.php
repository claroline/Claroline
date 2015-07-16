<?php

namespace FormaLibre\PresenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\PresenceBundle\Entity\Period;
use FormaLibre\PresenceBundle\Entity\Presence;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Group;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_job_admin_tool')")
 */
class AdminPresenceController extends Controller
{
    private $om;
    private $presenceRepo;
    private $periodRepo;
    private $groupdRepo;
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
        $this->presenceRepo       = $om->getRepository('FormaLibrePresenceBundle:Presence');
        $this->periodRepo         = $om->getRepository('FormaLibrePresenceBundle:Period');
        $this->groupRepo          = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User');  
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
        
        return array('user'=>$user, 'presences'=>$Presences, 'periods'=>$Periods);
         
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
 
        $Presences = $this->presenceRepo->findAll() ;
        $Periods = $this->periodRepo->findAll() ;
        $Groups = $this->groupRepo->findAll() ;
        $Users = $this->userRepo->findByGroup($classe) ;
        
        $presence = new Presence;
        
        $presForm = $this->createFormBuilder($presence)
            ->add('status','text', array('data' => '2'))
            ->getForm();
            
        $presForm->handleRequest($request);
        
        if ($request->getMethod() == 'POST')
            {
                $em = $this->getDoctrine()->getEntityManager();
                $actualPresence =new Presence();
                $actualPresence->setStatus("testperiod");
                $actualPresence->setUserTeacher($user);
                $actualPresence->setUserStudent($user);
                $dateFormat=new \DateTime($date);
                $actualPresence->setGroup($classe);
                $actualPresence->setPeriod($period);
                $actualPresence->setDate($dateFormat);
                $em->persist($actualPresence);
                $em->flush();
            }

        
        return array('presForm'=>$presForm->createView(),'user'=>$user, 'presences'=>$Presences, 'period'=>$period, 'date'=>$date, 'classe'=>$classe, 'groups'=>$Groups, 'users'=>$Users );
         
    }

}

