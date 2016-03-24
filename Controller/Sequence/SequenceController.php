<?php
namespace UJM\ExoBundle\Controller\Sequence;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Description of SequenceController.
 * @deprecated
 */
class SequenceController extends Controller
{
    /**
     * Render the Exercise player main view.
     * This view instaciate the angular player directive
     * @Route("/play/{id}", requirements={"id" = "\d+"}, name="ujm_exercise_play", options={"expose"=true})
     * @Method("GET")
     * @deprecated
     */
    public function playAction(Exercise $exercise)
    {
        // check authorisation
        $collection = new ResourceCollection([$exercise->getResourceNode()]);
        if (!$this->container->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedHttpException();
        }
        
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();   
        $paperManager = $this->get('ujm.exo.paper_manager');
        $apiData = $paperManager->openPaper($exercise, $user, false);
        $exo = json_encode($apiData['exercise']);
        $paper = json_encode($apiData['paper']);
        
        $u = array(
            'id' => $user->getId(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'admin' => $this->isExerciseAdmin($exercise)
        );
        
        return $this->render('UJMExoBundle:Sequence:play.html.twig', array(
                    '_resource' => $exercise,
                    'workspace' => $exercise->getResourceNode()->getWorkspace(),
                    'exercise' => $exo,
                    'paper' => $paper,
                    'user' => json_encode($u),
                    'currentStepIndex' => 0,
                    'duration' => $exercise->getDuration()
            )
        );
    }   
    
    
    /**
     * handle all AngularServices errors
     * @Route("/error/", name="ujm_sequence_error", options={"expose"=true})
     * @Method("GET")
     */
    public function sequenceError(Request $request)
    {
        $message = $request->get('message');
        $code = $request->get('code');

        switch ($code){
            case '403':
                throw new AccessDeniedHttpException($message);
                break;
            default :
                 throw new NotFoundHttpException($code . ' ' . $message);
        }
       
    }
    
    private function isExerciseAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode())); 
        return $this->container->get('security.authorization_checker')->isGranted('ADMINISTRATE', $collection);        
    }
}
