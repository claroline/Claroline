<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Paper;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * PaperController.
 */
class PaperController extends Controller
{

    /**
     * Render the paper list view
     * @Route("/{id}", requirements={"id" = "\d+"}, name="ujm_exercice_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function exercisePapersAction(Exercise $exercise)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // FAKE Data for development!!


        return $this->render('UJMExoBundle:Sequence:papers.html.twig', array(
                    '_resource' => $exercise
                        )
        );
    }

    /**
     * Get all papers for an exercise and a user
     * Todo : double check that the paper is available for the user since th exo id is given by a JS variable in papers.html.twig
     * @Route("/{id}/papers", requirements={"id" = "\d+"}, name="ujm_get_exercise_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function getExercisePapers(Exercise $exercise)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $sequence = $this->getExercise(2);
        $papers = array(
            array(
                'id' => '23',
                'exerciseId' => $exercise->getId(),
                'user' => $user->getUsername(),
                'number' => 1,
                'start' => '22/09/2015 - 09h18m01s',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'score' => 12
            ),
            array(
                'id' => '24',
                'exerciseId' => $exercise->getId(),
                'user' => $user->getUsername(),
                'number' => 2,
                'start' => '28/09/2015 - 13h38m52s',
                'end' => '',
                'interrupted' => true,
                'score' => ''
            )
        );
        
        $data = array ('papers' => $papers, 'sequence' => $sequence);
        
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $data;
        return new JsonResponse($response);
    }

    /**
     * Get the detail for one paper
     * @Route("/papers/{id}", requirements={"id" = "\d+"}, name="ujm_get_paper", options={"expose"=true})
     * @Method("GET")
     */
    public function getPaper(Paper $paper)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $sequence = $this->getExercise(2);
        // FAKE Data for development!!
        $papers = array(
            array(
                'id' => '23',
                'exerciseId' => $paper->getExercise()->getId(),
                'user' => $user->getUsername(),
                'number' => 1,
                'start' => '22/09/2015 - 09h18m01s ',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'score' => '0/20',
                'sequence' => $sequence
            ),
            array(
                'id' => '24',
                'exerciseId' => 9,
                'user' => $user->getUsername(),
                'number' => 2,
                'start' => '28/09/2015 - 13h38m52s',
                'end' => '',
                'interrupted' => true,
                'score' => '',
                'sequence' => $sequence
            )
        );


        $data = array ('paper' => $papers[0], 'sequence' => $sequence);
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $data;
        return new JsonResponse($response);
    }

    private function getExercise($metaNb)
    {
        $webImg1 = 'http://oumma.com/sites/default/files/bansky-flower-brick-thrower.jpg';
        $webImg2 = 'http://www.planetecampus.com/wp-content/uploads/2014/10/BhchDtlCIAALalT.jpg';
        $webImg3 = 'http://www.fubiz.net/wp-content/uploads/2012/07/You-are-not-Banksy17.jpg';

        $id = "9";

        if ($metaNb === 1) {
            $meta = array(
                "authors" => array(
                    array(
                        "name" => "John Doe",
                        "status" => "Tutor"
                    )
                ),
                "created" => "2015-09-11 10:43:56",
                "title" => "TEST",
                "description" => null,
                "keepSameQuestion" => false,
                "random" => false,
                "pick" => 0,
                "correctionMode" => 2,
            );
        } else if ($metaNb === 2) {
            $meta = array(
                "authors" => array(
                    array(
                        "name" => "John Doe",
                        "status" => "Tutor"
                    ),
                    array(
                        "name" => "Jane Doe",
                        "email" => "jane@mail.com",
                        "status" => "Professor"
                    )
                ),
                "license" => "CC",
                "created" => "2015-09-11 10:43:55",
                "title" => "TEST",
                "description" => "<h1>Lorem ipsum dolor sit amet</h1><p>Integer non tortor porta, facilisis odio vitae, condimentum ex. Ut dictum orci at enim consequat, vel iaculis augue posuere. Sed ullamcorper est et odio rhoncus mattis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse aliquam hendrerit nibh, ac mattis ante scelerisque at. Nam varius lorem nec ex malesuada pharetra. Quisque vulputate lacus ligula, in feugiat mauris sodales quis. Morbi ultricies dolor eu suscipit sollicitudin. Morbi vel congue sapien. Integer in lectus erat. Ut volutpat id nibh id semper. Vivamus ex turpis, iaculis id cursus et, ultricies vitae est. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam tincidunt quam consectetur mauris consectetur, in dictum diam hendrerit. Pellentesque maximus lectus mi. </p>",
                "pick" => 0,
                "random" => false,
                "maxAttempts" => 4,
                "correctionMode" => 1,
                "keepSameQuestion" => true
            );
        }


        $step1 = array(
            "id" => "1",
            // one item is one question
            "items" => array(
                array(
                    "id" => "7",
                    "type" => "application/x.choice+json",
                    "title" => "Simple question for dummies...",
                    "label" => "Which image shows a kid ?",
                    "random" => true,
                    "multiple" => false,
                    "feedback" => "Global feedback",
                    "hints" => array(
                        array(
                            "id" => "12",
                            "penalty" => 1
                        ),
                        array(
                            "id" => "54",
                            "penalty" => 2
                        )
                    ),
                    "choices" => array(
                        array(
                            "id" => "1",
                            "type" => "image/png",
                            "url" => $webImg1,
                            "meta" => array(
                                "description" => "Image 1"
                            )
                        ),
                        array(
                            "id" => "2",
                            "type" => "image/jpg",
                            "url" => $webImg2,
                            "meta" => array(
                                "description" => "Image 2"
                            )
                        ),
                        array(
                            "id" => "3",
                            "type" => "image/png",
                            "url" => $webImg3,
                            "meta" => array(
                                "description" => "Image 3"
                            )
                        )
                    )
                )
            )
        );

        $step2 = array(
            "id" => "1",
            "meta" => array(
                "authors" => array(
                    array(
                        "name" => "John Doe",
                        "status" => "Tutor"
                    )
                ),
                "license" => "CC",
                "created" => "2014-06-23"
            ),
            "items" => array(
                array(
                    "id" => "8",
                    "type" => "application/x.choice+json",
                    "title" => "Another simple question for dummies...",
                    "label" => "What is my prefered color ?",
                    "random" => true,
                    "multiple" => true,
                    "feedback" => "Global feedback",
                    "hints" => array(
                        array(
                            "id" => "27",
                            "penalty" => 1
                        ),
                        array(
                            "id" => "75",
                            "penalty" => 1.5
                        )
                    ),
                    "choices" => array(
                        array(
                            "id" => "3",
                            "type" => "text/html",
                            "data" => "<p>White</p>"
                        ),
                        array(
                            "id" => "4",
                            "type" => "text/html",
                            "data" => "<p>Yellow</p>"
                        ),
                        array(
                            "id" => "5",
                            "type" => "text/html",
                            "data" => "<p>Black</p>"
                        )
                    )
                )
            )
        );

        $steps = array($step1, $step2);
        $data = array("id" => $id, "meta" => $meta, "steps" => $steps);
        return $data;
    }

}
