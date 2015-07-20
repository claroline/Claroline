<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Sequence\Sequence;

/**
 * Description of StepController
 */
class StepController extends Controller {

    /**
     * update exercise player steps
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="ujm_steps_update", options = {"expose" = true})
     * @Method("POST")
     * @ParamConverter("Sequence", class="UJMExoBundle:Sequence\Sequence")
     * 
     */
    public function updateStepsAction(Sequence $resource) {

        if (false === $this->container->get('security.context')->isGranted('EDIT', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }
        $request = $this->container->get('request');
        // get request data
        $steps = $request->request->get('steps');

        // response
        $response = array();

        // update the exercise player pages
        try {
            $updated = $this->get('ujm_exo_bundle.manager.steps')->updateSteps($resource, $steps);
            $response['status'] = 'success';
            $response['messages'] = array();
            $response['data'] = $updated;
        } catch (\Exception $ex) {
            $response['status'] = 'error';
            $response['messages'] = $ex->getMessage();
            $response['data'] = null;
        }
        return new JsonResponse($response);
    }

    /**
     * This method should probably be placed in the QuestionController in the futur
     * It mimics the behaviour of $questions = $this->container->get('doctrine.orm.entity_manager')->getRepository('Questions')->findAll();
     * It respects the schema given by http://json-quiz.github.io/json-quiz/spec/
     * @Route("/questions/all", name="ujm_step_get_available_questions", options = {"expose" = true})
     * @Method("GET")
     * 
     */
    public function getAllQuestionAvailable() {

        $data = array();

        // CHOICE QUESTION
        $cq1 = array(
            "id" => "1",
            "type" => "application/x.choice+json",
            "title" => "Question 1",
            "choices" => array(
                array("id" => "1", "type" => "image/png", "url" => "http://domain.com/image-1.png", "meta" => array("description" => "Image 1")),
                array("id" => "2", "type" => "image/png", "url" => "http://domain.com/image-2.png", "meta" => array("description" => "Image 2")),
                array("id" => "3", "type" => "image/png", "url" => "http://domain.com/image-3.png", "meta" => array("description" => "Image 3")),
            ),
            "random" => false,
            "multiple" => false,
            "solutions" => array(
                array("id" => "1", "score" => 2),
                array("id" => "3", "score" => 1)
            )
        );
        $cq2 = array(
            "id" => "2",
            "type" => "application/x.choice+json",
            "meta" => array(
                "authors" => array(
                    "name" => "John Doe", "status" => "Tutor"
                )
                ,
                "license" => "CC",
                "created" => "2014-06-23"
            ),
            "objects" => array(
                array(
                    "id" => "1",
                    "type" => "text/html",
                    "data" => "<p>Lorem ipsum dolor sit amet</p>",
                    "meta" => array(
                        "title" => "Lorem sample"
                    )
                )
            ),
            "resources" => array(
                array(
                    "id" => "2",
                    "type" => "application/pdf",
                    "url" => "http://domain.com/syllabus.pdf"
                )
            ),
            "title" => "Question 2",
            "choices" => array(
                array("id" => "3", "type" => "image/png", "encoding" => "base64", "data" => "f47544a4211f454e12"),
                array("id" => "4", "type" => "image/png", "encoding" => "base64", "data" => "944fc234fdf454a454213"),
                array("id" => "5", "type" => "image/png", "encoding" => "base64", "data" => "ce5423f23e51a45454962")
            ),
            "random" => false,
            "multiple" => false,
            "hints" => array(
                array("id" => "3", "text" => "Lorem", "penalty" => 1),
                array("id" => "5", "text" => "Ipsum", "penalty" => 1.5)
            )
        );

        // MATCH QUESTION
        $mq1 = array(
            "id" => "3",
            "title" => "Question 3",
            "type" => "application/x.choice+json",
            "choices" => array(
                array("id" => "1", "type" => "image/png", "url" => "http://domain.com/image-1.png"),
                array("id" => "2", "type" => "image/png", "url" => "http://domain.com/image-2.png"),
                array("id" => "3", "type" => "image/png", "url" => "http://domain.com/image-3.png")
            ),
            "random" => true,
            "multiple" => true
        );
        $mq2 = array(
            "id" => "4",
            "type" => "application/x.match+json",
            "meta" => array(
                "authors" => array(
                    array("name" => "John Doe", "status" => "Tutor"),
                    array("name" => "Jane Doe", "email" => "jane@mail.com", "status" => "Professor")
                ),
                "license" => "CC",
                "created" => "2014-06-23"
            ),
            "title" => "Question 4",
            "objects" => array(
                array(
                    "id" => "1",
                    "type" => "image/png",
                    "url" => "http://domain.com/image.png",
                    "meta" => array("title" => "Lorem sample")
                )
            ),
            "resources" => array(
                array("id" => "2", "type" => "application/pdf", "url" => "http://domain.com/syllabus.pdf")
            ),
            "firstSet" => array(
                array("id" => "3", "type" => "text/plain", "data" => "Item A"),
                array("id" => "4", "type" => "text/plain", "data" => "Item B")
            ),
            "secondSet" => array(
                array("id" => "5", "type" => "image/png", "url" => "http://domain.com/image-c.png"),
                array("id" => "6", "type" => "image/png", "url" => "http://domain.com/image-d.png")
            ),
            "solutions" => array(
                array("firstId" => "3", "secondId" => "6", "score" => 1.5),
                array("firstId" => "4", "secondId" => "5", "score" => 1)
            ),
            "feedback" => "Lorem ipsum dolor sit amet."
        );

        // SORT QUESTION
        $sq1 = array(
            "id" => "5", 
            "type" => "application/x.sort+json", 
            "title" => "Question 5",
            "items" => array(
                array("id" => "2", "type" => "image/jpg", "url" => "http://domain.com/image-1.jpg"),
                array("id" => "3", "type" => "image/jpg", "url" => "http://domain.com/image-2.jpg"),
                array("id" => "4", "type" => "image/jpg", "url" => "http://domain.com/image-3.jpg")
            ), 
            "solution" => array(
                "itemIds" => ["3", "4", "2"],
                "itemScore" => 1.5
            )
        );
        $sq2 = array(
           "id" => "6", 
            "type" => "application/x.sort+json", 
            "meta" => array(
                "authors" => array(
                    "name" => "John Doe"
                )
            ), 
            "title" => "Question 6", 
            "objects" => array(
                array(
                    "id" => "42", 
                    "type" => "text/plain", 
                    "url" => "http://domain.com/text.txt"
                    )
            ), 
            "items" => array(
                array("id" => "2", "type" => "image/jpg", "url" => "http://domain.com/image-1.jpg"),
                array("id" => "3", "type" => "image/jpg", "url" => "http://domain.com/image-2.jpg"),
                array("id" => "4", "type" => "image/jpg", "url" => "http://domain.com/image-3.jpg")
            ),
            "solution" => array(
                "itemIds" => ["3", "4", "2"],
                "itemScore" => 1.5
            ),
            "feedback" => "Lorem ipsum dolor sit amet" 
        );

        // CLOZE QUESTION
        $clq1 = array(
            "id" => "7",
            "type" => "application/x.cloze+json",
            "title" => "Question 7",
            "text" => "Lorem ipsum [[1]] sit amet.",
            "solutions" => array("holeId" => "1", "answers" => array("dolor"), "score" => 2)
        );
        $clq2 = array(
            "id" => "8",
            "type" => "application/x.cloze+json",
            "title" => "Question 8",
            "text" => "Lorem [[1]] dolor sit [[2]].",
            "holes" => array(
                array("id" => "1", "choices" => ["foo", "ipsum", "bar"]),
                array("id" => "2", "size" => 10)
            ),
            "solutions" => array(
                array("holeId" => "1", "answers" => ["ipsum"], "score" => 1.5),
                array("holeId" => "2", "answers" => ["amet", "consecitur", "nunc"], "score" => 3.5)
            )
        );

        array_push($data, $cq1, $cq2, $mq1, $mq2, $sq1, $sq2, $clq1, $clq2);
        return new JsonResponse($data);
    }

}
