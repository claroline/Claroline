<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Paper;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * PaperController.
 */
class PaperController extends Controller
{
    /**
     * Render the paper list view.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="ujm_exercice_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function exercisePapersAction(Exercise $exercise)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        return $this->render('UJMExoBundle:Sequence:papers.html.twig', array(
                    '_resource' => $exercise,
                        )
        );
    }

    /**
     * Get all papers for an exercise and a user
     * Todo : double check that the paper is available for the user since th exo id is given by a JS variable in papers.html.twig.
     *
     * @Route("/{id}/papers", requirements={"id" = "\d+"}, name="ujm_get_exercise_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function getExercisePapers(Exercise $exercise)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // get sequence data... or not... depends on the choice we do
        // we can have separate data or mix the data in the paper(s) object... 
        // In any case some data that are not paper only wont be in sequence... I would suggest to mix all the needed data in the paper object
        $sequence = $this->getExercise(2);
        $webImg1 = 'http://oumma.com/sites/default/files/bansky-flower-brick-thrower.jpg';
        $webImg2 = 'http://www.planetecampus.com/wp-content/uploads/2014/10/BhchDtlCIAALalT.jpg';
        $webImg3 = 'http://www.fubiz.net/wp-content/uploads/2012/07/You-are-not-Banksy17.jpg';

        $papers = array(
            array(
                // add exercise title ?
                // add exercise id ?
                'id' => '23', // paper id
                'tryNumber' => 1,
                'user' => $user->getUsername(),
                'number' => 1,  // equals tryNumber ??
                'start' => '22/09/2015 - 09h18m01s',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'questions' => array(
                    array(
                        'id' => '7',
                        'type' => 'application/x.choice+json',
                        'title' => 'Simple question for dummies...',
                        'label' => 'Which image shows a kid ?',
                        'feedback' => 'Come on this one is easy',
                        'mark' => 0,
                        // student used hints... all data needed ? (except for text ?)
                        'hints' => array(
                            array(
                                'id' => '12',
                                'penalty' => 1.5,
                                'text' => "I'm hint number 12",
                            ),
                        ),
                        // question solution
                        'solution' => array("2"),
                        'choices' => array(
                            array(
                                'id' => '1',
                                'type' => 'image/png',
                                'url' => $webImg1,
                                'meta' => array(
                                    'description' => 'Image 1',
                                ),                                
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                            array(
                                'id' => '2',
                                'type' => 'image/jpg',
                                'url' => $webImg2,
                                'meta' => array(
                                    'description' => 'Image 2',
                                ),                                                                
                                'score' => 6,
                                'feedback' => 'Yes this is a kid'
                            ),
                            array(
                                'id' => '3',
                                'type' => 'image/png',
                                'url' => $webImg3,
                                'meta' => array(
                                    'description' => 'Image 3',
                                ),                               
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                        ),
                        // student answer
                        'answer' => ['2'],
                    ),
                    array(
                        'id' => '8',
                        'type' => 'application/x.choice+json',
                        'title' => 'Another simple question for dummies...',
                        'label' => 'What is my prefered color ?',
                        'feedback' => 'You knew it...',
                        'mark' => 0,
                        'hints' => array(
                            array(
                                'id' => '27',
                                'penalty' => 0.5,
                                "text" => "I'm hint number 27"
                            ),
                            array(
                                'id' => '75',
                                'penalty' => 0.5,
                                "text" => "I'm hint number 75"
                            ),
                        ),
                        'choices' => array(
                            array(
                                'id' => '3',
                                'type' => 'text/html',
                                'data' => '<p>White</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course'
                            ),
                            array(
                                'id' => '4',
                                'type' => 'text/html',
                                'data' => '<p>Yellow</p>',                              
                                'score' => 0,
                                'feedback' => 'Of course not'
                            ),
                            array(
                                'id' => '5',
                                'type' => 'text/html',
                                'data' => '<p>Black</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course!'
                            ),
                        ),
                        'solution' => array("3", "5"),
                        // student answer
                        'answer' => ['3', '5'],
                    ),
                ),
            ),array(
                // add exercise title ?
                // add exercise id ?
                'id' => '24', // paper id
                'tryNumber' => 2,
                'user' => $user->getUsername(),
                'number' => 2, // equals tryNumber ??
                'start' => '22/09/2015 - 11h11m11s',
                'end' => '',
                'interrupted' => true,
                'questions' => array(
                    array(
                        'id' => '7',
                        'type' => 'application/x.choice+json',
                        'title' => 'Simple question for dummies...',
                        'label' => 'Which image shows a kid ?',
                        'feedback' => 'Come on this one is easy',
                        'mark' => 0,
                        // student used hints... all data needed ? (except for text ?)
                        'hints' => array(
                            array(
                                'id' => '12',
                                'penalty' => 1.5,
                                'text' => "I'm hint number 12",
                            ),
                        ),
                        // question solution
                        'solution' => array("2"),
                        'choices' => array(
                            array(
                                'id' => '1',
                                'type' => 'image/png',
                                'url' => $webImg1,
                                'meta' => array(
                                    'description' => 'Image 1',
                                ),                                
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                            array(
                                'id' => '2',
                                'type' => 'image/jpg',
                                'url' => $webImg2,
                                'meta' => array(
                                    'description' => 'Image 2',
                                ),                                                                
                                'score' => 6,
                                'feedback' => 'Yes this is a kid'
                            ),
                            array(
                                'id' => '3',
                                'type' => 'image/png',
                                'url' => $webImg3,
                                'meta' => array(
                                    'description' => 'Image 3',
                                ),                               
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                        ),
                        // student answer
                        'answer' => ['2'],
                    ),
                    array(
                        'id' => '8',
                        'type' => 'application/x.choice+json',
                        'title' => 'Another simple question for dummies...',
                        'label' => 'What is my prefered color ?',
                        'feedback' => 'You knew it...',
                        'mark' => 0,
                        'hints' => array(
                            array(
                                'id' => '27',
                                'penalty' => 0.5,
                            ),
                            array(
                                'id' => '75',
                                'penalty' => 0.5,
                            ),
                        ),
                        'choices' => array(
                            array(
                                'id' => '3',
                                'type' => 'text/html',
                                'data' => '<p>White</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course'
                            ),
                            array(
                                'id' => '4',
                                'type' => 'text/html',
                                'data' => '<p>Yellow</p>',                              
                                'score' => 0,
                                'feedback' => 'Of course not'
                            ),
                            array(
                                'id' => '5',
                                'type' => 'text/html',
                                'data' => '<p>Black</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course!'
                            ),
                        ),
                        "solution" => array("3", "5"),
                        // student answer
                        "answer" => ["4", "5"],
                    ),
                ),
            )
        );

        $data = array('papers' => $papers, 'sequence' => $sequence);

        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $data;

        return new JsonResponse($response);
    }

    /**
     * Get the detail for one paper.
     *
     * @Route("/papers/{id}", requirements={"id" = "\d+"}, name="ujm_get_paper", options={"expose"=true})
     * @Method("GET")
     */
    public function getPaper(Paper $paper)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // FAKE Data for development!!
        $sequence = $this->getExercise(2);
         $webImg1 = 'http://oumma.com/sites/default/files/bansky-flower-brick-thrower.jpg';
        $webImg2 = 'http://www.planetecampus.com/wp-content/uploads/2014/10/BhchDtlCIAALalT.jpg';
        $webImg3 = 'http://www.fubiz.net/wp-content/uploads/2012/07/You-are-not-Banksy17.jpg';
        $_paper = array(
                // add exercise title ?
                // add exercise id ?
                'id' => '23', // paper id
                'tryNumber' => 1,
                'user' => $user->getUsername(),
                'number' => 1,
                'start' => '22/09/2015 - 09h18m01s',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'questions' => array(
                    array(
                        'id' => '7',
                        'type' => 'application/x.choice+json',
                        'title' => 'Simple question for dummies',
                        'label' => 'Which image shows a kid ?',
                        'feedback' => 'Come on this one is easy',
                        'mark' => 0,
                        // student used hints... all data needed ? (except for text ?)
                        'hints' => array(
                            array(
                                'id' => '12',
                                'penalty' => 1.5,
                                'text' => "I'm hint number 12",
                            ),
                        ),
                        // question solution
                        'solution' => array("2"),
                        'choices' => array(
                            array(
                                'id' => '1',
                                'type' => 'image/png',
                                'url' => $webImg1,
                                'meta' => array(
                                    'description' => 'Image 1',
                                ),                                
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                            array(
                                'id' => '2',
                                'type' => 'image/jpg',
                                'url' => $webImg2,
                                'meta' => array(
                                    'description' => 'Image 2',
                                ),                                                                
                                'score' => 6,
                                'feedback' => 'Yes this is a kid'
                            ),
                            array(
                                'id' => '3',
                                'type' => 'image/png',
                                'url' => $webImg3,
                                'meta' => array(
                                    'description' => 'Image 3',
                                ),                               
                                'score' => 0,
                                'feedback' => 'No this is not a kid'
                            ),
                        ),
                        // student answer
                        'answer' => ['1'],
                    ),
                    array(
                        'id' => '8',
                        'type' => 'application/x.choice+json',
                        'title' => 'Another simple question for dummies',
                        'label' => 'What is my prefered color ?',
                        'feedback' => 'You knew it???',
                        'mark' => 0,
                        'hints' => array(
                            array(
                                'id' => '27',
                                'penalty' => 0.5,                                
                                "text" => "I'm hint number 27"
                            ),
                            array(
                                'id' => '75',
                                'penalty' => 0.5,
                                "text" => "I'm hint number 75"
                            ),
                        ),
                        'choices' => array(
                            array(
                                'id' => '3',
                                'type' => 'text/html',
                                'data' => '<p>White</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course'
                            ),
                            array(
                                'id' => '4',
                                'type' => 'text/html',
                                'data' => '<p>Yellow</p>',                              
                                'score' => 0,
                                'feedback' => 'Of course not'
                            ),
                            array(
                                'id' => '5',
                                'type' => 'text/html',
                                'data' => '<p>Black</p>',                              
                                'score' => 1.5,
                                'feedback' => 'Of course!'
                            ),
                        ),
                        'solution' => array("3", "5"),
                        // student answer
                        'answer' => ['3', '5'],
                    ),
                ),
            );

        $data = array('paper' => $_paper, 'sequence' => $sequence);
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

        $id = '9';

        if ($metaNb === 1) {
            $meta = array(
                'authors' => array(
                    array(
                        'name' => 'John Doe',
                        'status' => 'Tutor',
                    ),
                ),
                'created' => '2015-09-11 10:43:56',
                'title' => 'TEST',
                'description' => null,
                'keepSameQuestion' => false,
                'random' => false,
                'pick' => 0,
                'correctionMode' => 2,
            );
        } elseif ($metaNb === 2) {
            $meta = array(
                'authors' => array(
                    array(
                        'name' => 'John Doe',
                        'status' => 'Tutor',
                    ),
                    array(
                        'name' => 'Jane Doe',
                        'email' => 'jane@mail.com',
                        'status' => 'Professor',
                    ),
                ),
                'license' => 'CC',
                'created' => '2015-09-11 10:43:55',
                'title' => 'TEST',
                'description' => '<h1>Lorem ipsum dolor sit amet</h1><p>Integer non tortor porta, facilisis odio vitae, condimentum ex. Ut dictum orci at enim consequat, vel iaculis augue posuere. Sed ullamcorper est et odio rhoncus mattis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse aliquam hendrerit nibh, ac mattis ante scelerisque at. Nam varius lorem nec ex malesuada pharetra. Quisque vulputate lacus ligula, in feugiat mauris sodales quis. Morbi ultricies dolor eu suscipit sollicitudin. Morbi vel congue sapien. Integer in lectus erat. Ut volutpat id nibh id semper. Vivamus ex turpis, iaculis id cursus et, ultricies vitae est. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam tincidunt quam consectetur mauris consectetur, in dictum diam hendrerit. Pellentesque maximus lectus mi. </p>',
                'pick' => 0,
                'random' => false,
                'maxAttempts' => 4,
                'correctionMode' => 1,
                'keepSameQuestion' => true,
            );
        }

        $step1 = array(
            'id' => '1',
            // one item is one question
            'items' => array(
                array(
                    'id' => '7',
                    'type' => 'application/x.choice+json',
                    'title' => 'Simple question for dummies...',
                    'label' => 'Which image shows a kid ?',
                    'random' => true,
                    'multiple' => false,
                    'feedback' => 'Global feedback',
                    'hints' => array(
                        array(
                            'id' => '12',
                            'penalty' => 1,
                        ),
                        array(
                            'id' => '54',
                            'penalty' => 2,
                        ),
                    ),
                    'choices' => array(
                        array(
                            'id' => '1',
                            'type' => 'image/png',
                            'url' => $webImg1,
                            'meta' => array(
                                'description' => 'Image 1',
                            ),
                        ),
                        array(
                            'id' => '2',
                            'type' => 'image/jpg',
                            'url' => $webImg2,
                            'meta' => array(
                                'description' => 'Image 2',
                            ),
                        ),
                        array(
                            'id' => '3',
                            'type' => 'image/png',
                            'url' => $webImg3,
                            'meta' => array(
                                'description' => 'Image 3',
                            ),
                        ),
                    ),
                ),
            ),
        );

        $step2 = array(
            'id' => '1',
            'meta' => array(
                'authors' => array(
                    array(
                        'name' => 'John Doe',
                        'status' => 'Tutor',
                    ),
                ),
                'license' => 'CC',
                'created' => '2014-06-23',
            ),
            'items' => array(
                array(
                    'id' => '8',
                    'type' => 'application/x.choice+json',
                    'title' => 'Another simple question for dummies...',
                    'label' => 'What is my prefered color ?',
                    'random' => true,
                    'multiple' => true,
                    'feedback' => 'Global feedback',
                    'hints' => array(
                        array(
                            'id' => '27',
                            'penalty' => 1,
                        ),
                        array(
                            'id' => '75',
                            'penalty' => 1.5,
                        ),
                    ),
                    'choices' => array(
                        array(
                            'id' => '3',
                            'type' => 'text/html',
                            'data' => '<p>White</p>',
                        ),
                        array(
                            'id' => '4',
                            'type' => 'text/html',
                            'data' => '<p>Yellow</p>',
                        ),
                        array(
                            'id' => '5',
                            'type' => 'text/html',
                            'data' => '<p>Black</p>',
                        ),
                    ),
                ),
            ),
        );

        $steps = array($step1, $step2);
        $data = array('id' => $id, 'meta' => $meta, 'steps' => $steps);

        return $data;
    }
}
